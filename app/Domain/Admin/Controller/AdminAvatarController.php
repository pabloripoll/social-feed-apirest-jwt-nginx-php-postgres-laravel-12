<?php

namespace App\Domain\Admin\Controller;

use App\Domain\Admin\Models\AdminAvatar;
use App\Domain\Admin\Requests\AdminAvatarRequest;
use App\Http\Services\File\FileService;
use App\Http\Services\Storage\Local\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminAvatarController
{
    /**
     * GET /api/v1/account/avatars
     */
    public function list(): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();
        $user->load(['adminAvatars']);

        $avatarsCount = $user->adminAvatars->count();

        $response = [
            'message' => 'Admin has '.$avatarsCount.' avatar files.',
            'total' => $avatarsCount,
            'result' => $user->adminAvatars?->toArray() ?? 0,
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/account/avatars/{avatar_uid}
     */
    public function read(int $avatar_uid): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();

        $avatar = AdminAvatar::query()
            ->where('uid', $avatar_uid)
            ->where('user_id', $user->id)
            ->first();
        if (! $avatar) {
            return response()->json([
                'message' => 'Avatar not found.',
                'error' => 'avatar_not_found',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        return response()->json($avatar->toArray(), JsonResponse::HTTP_OK);
    }

    /**
     * PUT /api/v1/account/avatars/{avatar_uid}/select
     */
    public function select(int $avatar_uid): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();
        $user->load(['adminAvatars']);

        $exists = false;
        foreach ($user->adminAvatars as $avatar) {
            if ($avatar->uid == $avatar_uid) {
                $exists = true;

                continue;
            }
        }

        if (! $exists) {
            return response()->json([
                'message' => 'Avatar not found.',
                'error' => 'avatar_not_found',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $selected = null;
        foreach ($user->adminAvatars as $avatar) {
            if ($avatar->uid == $avatar_uid) {
                $avatar->is_selected = true;
                $avatar->save();
                $selected = $avatar;
            } else {
                $avatar->is_selected = false;
                $avatar->save();
            }
        }

        return response()->json($selected, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/account/avatars/selected
     */
    public function selected(): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();
        $user->load(['adminAvatar']);

        $avatar = $user->adminAvatar;
        if (! $avatar) {
            return response()->json([
                'message' => 'No avatar has been selected.',
                'error' => 'avatar_not_selected',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        return response()->json($avatar->toArray(), JsonResponse::HTTP_OK);
    }

    /**
     * POST /api/v1/account/avatars
     */
    public function upload(Request $request): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();
        $user->load(['admin', 'adminProfile']);

        $formRequest = new AdminAvatarRequest;
        $validator = Validator::make(
            $request->all(),
            $formRequest->rules(),
            $formRequest->messages()
        );
        if ($validator->fails()) {
            $errors = (array) $validator->errors()->messages();
            $field = array_key_first($errors);

            return response()->json(['message' => $errors[$field][0], 'error' => $field], JsonResponse::HTTP_NOT_ACCEPTABLE);
        }

        $file = (new FileService)->fromRequest($request, 'file');

        if ($file->size_mega > 1) { // limit value is referenced to php.ini
            return response()->json(
                [
                    'message' => 'Max. file size is 1 MB.',
                    'error' => 'file_size_error',
                ],
                JsonResponse::HTTP_NOT_ACCEPTABLE
            );
        }

        $max = 10;
        $total = AdminAvatar::query()
            ->where('user_id', $user->id)
            ->count();
        if ($total >= $max) {
            return response()->json(
                [
                    'message' => 'Maximum avatars upload per admin is '.$max.'.',
                    'error' => 'max_uploads_reached',
                ],
                JsonResponse::HTTP_NOT_ACCEPTABLE
            );
        }

        $bucket = (object) [
            'path' => 'admin/avatars', // -> /var/www/storage/app/public/avatar
            'name' => $user->admin->uid.'_'.now()->timestamp.'.'.$file->extension,
        ];

        $storage = (new StorageService)->put($file, $bucket);
        if (! $storage) {
            return response()->json(
                [
                    'message' => 'File could not been transferred.',
                    'error' => 'file_transfer_error',
                ],
                JsonResponse::HTTP_CONFLICT
            );
        }

        $avatar = new AdminAvatar;
        $avatar->user_id = $user->id;
        $avatar->position = $total + 1;
        $avatar->extension = $storage->extension;
        $avatar->path = $storage->path;
        $avatar->name = $storage->name;
        $avatar->title = substr($file->name, 0, strrpos($file->name, '.'));
        $avatar->slug = Str::slug($user->adminProfile->nickname);
        $avatar->url = '/static/'.$storage->path.'/'.$storage->name;
        $avatar->save();

        return response()->json(
            [
                'message' => 'File successfully uploaded.',
                'uid' => $avatar->uid,
                'url' => $avatar->url,
                'total_uploads' => $total + 1,
            ],
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * DELETE /api/v1/account/avatars/{avatar_uid}
     */
    public function delete(int $avatar_uid): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();

        $avatar = AdminAvatar::query()
            ->where('uid', $avatar_uid)
            ->where('user_id', $user->id)
            ->first();
        if (! $avatar) {
            return response()->json(
                [
                    'message' => 'Avatar not found.',
                    'error' => 'avatar_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $bucket = (new StorageService)->delete($avatar);
        if ($bucket->storage) {
            return response()->json(
                [
                    'message' => 'Avatar could not be deleted.',
                    'error' => 'deletion_failed',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $avatar->delete();

        $bucket->type = 'image';

        $response = [
            'message' => 'Avatar file deleted.',
            'storage' => $bucket,
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
