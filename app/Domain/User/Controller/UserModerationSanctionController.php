<?php

namespace App\Domain\User\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Domain\User\Models\UserModeration;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\User\Models\UserModerationSanction;
use App\Domain\User\Requests\UserModerationSanctionCreateRequest;
use App\Domain\User\Requests\UserModerationSanctionUpdateRequest;

class UserModerationSanctionController extends Controller
{
    /**
     * GET /api/v1/moderations/sanctions
     */
    public function listing(Request $request): JsonResponse
    {
        $response = UserModerationSanction::select(['key','title'])
            ->orderBy('position', 'asc')
            ->get()
            ->pluck('key', 'title')
            ->toArray();

        return response()->json($response, JsonResponse::HTTP_CREATED);
    }

    /**
     * POST /api/v1/moderations/sanctions
     */
    public function create(Request $request): JsonResponse
    {
        $formRequest = new UserModerationSanctionCreateRequest;
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

        $sanction = UserModerationSanction::where('key', $validated['key'])->first();
        if ($sanction) {
            return response()->json([
                    'message' => 'Sanction key already exists.',
                    'error' => 'category_already_exists',
                ],
                JsonResponse::HTTP_NOT_ACCEPTABLE
            );
        }

        $sanction = new UserModerationSanction;
        $sanction->level        = $validated['level'];
        $sanction->position     = $validated['position'];
        $sanction->title        = $validated['title'];
        $sanction->description  = $validated['description'];
        $sanction->save();

        $response = [
            'id'            => $sanction->id,
            'key'           => $sanction->key,
            'position'      => $sanction->position,
            'title'         => $sanction->title,
            'description'   => $sanction->description,
            'created_at'    => $sanction->created_at->format('Y-m-d H:i:s'),
            'updated_at'    => $sanction->updated_at->format('Y-m-d H:i:s'),
        ];

        return response()->json($response, JsonResponse::HTTP_CREATED);
    }

    /**
     * GET /api/v1/moderations/sanctions/{id}
     */
    public function read(int $id): JsonResponse
    {
        $sanction = UserModerationSanction::where('id', $id)->first();
        if (! $sanction) {
            return response()->json([
                    'message' => 'Sanction not found.',
                    'error' => 'category_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $response = [
            'id'            => $sanction->id,
            'key'           => $sanction->key,
            'position'      => $sanction->position,
            'title'         => $sanction->title,
            'description'   => $sanction->description,
            'created_at'    => $sanction->created_at->format('Y-m-d H:i:s'),
            'updated_at'    => $sanction->updated_at->format('Y-m-d H:i:s'),
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * PATCH /api/v1/moderations/sanctions/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $formRequest = new UserModerationSanctionUpdateRequest;
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
        if (count($validated) < 1) {
            return response()->json([
                    'message' => 'At least one parameter must exists to update category.',
                    'error' => 'no_category_params',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $sanction = UserModerationSanction::where('id', $id)->first();
        if (! $sanction) {
            return response()->json([
                    'message' => 'Sanction not found.',
                    'error' => 'category_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        ! isset($validated['key']) ? : $sanction->key = $validated['key'];
        ! isset($validated['position']) ? : $sanction->position = $validated['position'];
        ! isset($validated['title']) ? : $sanction->title = $validated['title'];
        ! isset($validated['description']) ? : $sanction->description = $validated['description'];
        $sanction->save();

        $response = [
            'id'            => $sanction->id,
            'key'           => $sanction->key,
            'position'      => $sanction->position,
            'title'         => $sanction->title,
            'description'   => $sanction->description,
            'created_at'    => $sanction->created_at->format('Y-m-d H:i:s'),
            'updated_at'    => $sanction->updated_at->format('Y-m-d H:i:s'),
        ];

        return response()->json($response, JsonResponse::HTTP_ACCEPTED);
    }

    /**
     * DELETE /api/v1/moderations/sanctions/{id}
     */
    public function delete(int $id): JsonResponse
    {
        $sanction = UserModeration::query()
            ->with(['moderations'])
            ->where('id', $id)
            ->first();

        if (! $sanction) {
            return response()->json([
                    'message' => 'Sanction not found.',
                    'error' => 'category_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        if ($sanction->moderations->count() >= 1) {
            return response()->json([
                    'message' => 'Sanction has related moderations.',
                    'error' => 'category_has_related_moderations',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $legacySanction = $sanction;

        $sanction->delete();

        $response = [
            'message' => 'Moderation Sanction -'.$legacySanction->key.'- has been successfully deleted.',
            'id' => $legacySanction->id,
            'key' => $legacySanction->key,
        ];

        return response()->json($response, JsonResponse::HTTP_ACCEPTED);
    }
}
