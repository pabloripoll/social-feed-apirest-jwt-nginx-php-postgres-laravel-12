<?php

namespace App\Domain\Member\Controller;

use Illuminate\Http\Request;
use App\Domain\Feed\Models\FeedMedia;
use App\Domain\Feed\Models\FeedPost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Services\File\FileService;
use App\Domain\Member\Requests\MemberFeedMediaRequest;
use App\Http\Services\Storage\Local\StorageService;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\JsonResponse;

class MemberFeedMediaController
{
    /**
     * GET /api/v1/account/feed/posts/{post_uid}/media/{media_uid?}
     */
    public function readPostMedia(int $post_uid, ?int $media_uid = null): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();

        $post = FeedPost::query()
            ->with(['media'])
            ->where('uid', $post_uid)
            ->where('user_id', $user->id)
            ->first();
        if (! $post) {
            return response()->json([
                    'message' => 'Feed post not found.',
                    'error' => 'post_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        if ($post->is_banned) {
            return response()->json([
                    'message' => 'Feed post cannot be edited.',
                    'error' => 'post_not_editable',
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $query = FeedMedia::query()
            ->when($media_uid, function ($query, $media_uid) {
                $query->where('uid', $media_uid);
            })
            ->where('user_id', $user->id)
            ->where('post_id', $post->id);

        $mediaCount = $query->count();

        if ($mediaCount < 1) {
            return response()->json(
                [
                    'message' => 'No feed post media was found.',
                    'error' => 'no_media_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $media = $query->get();

        $response = [
            'message' => 'Feed post has '. $mediaCount. ' media files.',
            'total'   => $mediaCount,
            'result'  => $media->toArray(),
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * POST /api/v1/account/feed/posts/{post_uid}/media
     */
    public function uploadPostMedia(Request $request, int $post_uid): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();
        $user->load(['member']);

        $formRequest = new MemberFeedMediaRequest;
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
        $validated = $validator->validated();

        $file = (new FileService)->fromRequest($request, 'media');
        if (! $file) {
            return response()->json(
                [
                    'message' => 'File has not been uploaded.',
                    'error' => 'file_upload_error',
                ],
                JsonResponse::HTTP_NOT_ACCEPTABLE
            );
        }

        if ($file->size_mega > 3) { // limit value is referenced to php.ini
            return response()->json(
                [
                    'message' => 'Max. file size is 6 MB.',
                    'error' => 'file_size_error',
                ],
                JsonResponse::HTTP_NOT_ACCEPTABLE
            );
        }

        $type = $validated['type'];

        $post = FeedPost::query()
            ->with(['media'])
            ->where('uid', $post_uid)
            ->where('user_id', $user->id)
            ->first();
        if (! $post) {
            return response()->json([
                    'message' => 'Feed post not found.',
                    'error' => 'post_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $max = 1;
        $total = FeedMedia::query()
            ->where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->count();
        if ($total >= $max) {
            return response()->json(
                [
                    'message' => 'Maximum uploads per Feed Post is '.$max.'.',
                    'error' => 'max_uploads_reached',
                ],
                JsonResponse::HTTP_NOT_ACCEPTABLE
            );
        }

        $bucket = (object) [
            'path' => 'feed/posts', //-> ./storage/app/public/
            'name' => $user->member->uid.'_'.now()->timestamp.'.'.$file->extension,
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

        $slug = $post->title ?? $user->member->nickname;

        $media = new FeedMedia;
        $media->user_id = $user->id;
        $media->post_id = $post->id;
        $media->position = $total + 1;
        $media->type = $type;
        $media->extension = $storage->extension;
        $media->path = $storage->path;
        $media->name = $storage->name;
        $media->title = substr($file->name, 0, strrpos($file->name, '.'));
        $media->slug = Str::slug($slug);
        $media->url = $storage->url;
        $media->save();

        return response()->json(
            [
                'message' => 'File successfully uploaded.',
                'uid' => $media->uid,
                'url' => $storage->url,
                'total_uploads' => $total + 1
            ],
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * DELETE /api/v1/account/feed/posts/9022780/media/{all|media_uid}
     */
    public function deletePostMedia(int $post_uid, ?int $media_uid = null): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();

        $post = FeedPost::query()
            ->with(['media'])
            ->where('uid', $post_uid)
            ->where('user_id', $user->id)
            ->first();
        if (! $post) {
            return response()->json([
                    'message' => 'Feed post not found.',
                    'error' => 'post_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        if ($post->is_banned) {
            return response()->json([
                    'message' => 'Feed post cannot be edited.',
                    'error' => 'post_not_editable',
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $query = FeedMedia::query()
            ->when($media_uid, function ($query, $media_uid) {
                $query->where('uid', $media_uid);
            })
            ->where('user_id', $user->id)
            ->where('post_id', $post->id);

        $mediaCount = $query->count();

        if ($mediaCount < 1) {
            return response()->json(
                [
                    'message' => 'No feed post media was found.',
                    'error' => 'no_media_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $media = $query->get();
        $storage = [];
        foreach ($media as $object) {
            $storage[] = (new StorageService)->delete($object);
        }

        $response = [
            'message' => $mediaCount. ' feed post media was deleted.',
            'storage' => $storage,
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
