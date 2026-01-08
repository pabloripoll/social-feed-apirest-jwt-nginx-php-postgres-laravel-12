<?php

namespace App\Domain\User\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Domain\User\Models\UserModeration;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\User\Models\UserModerationCategory;
use App\Domain\User\Requests\UserModerationCategoryCreateRequest;
use App\Domain\User\Requests\UserModerationCategoryUpdateRequest;

class UserModerationCategoryController extends Controller
{
    /**
     * GET /api/v1/moderations/categories
     */
    public function listing(Request $request): JsonResponse
    {
        $response = UserModerationCategory::select(['key','title'])
            ->orderBy('position', 'asc')
            ->get()
            ->pluck('key', 'title')
            ->toArray();

        return response()->json($response, JsonResponse::HTTP_CREATED);
    }

    /**
     * POST /api/v1/moderations/categories
     */
    public function create(Request $request): JsonResponse
    {
        $formRequest = new UserModerationCategoryCreateRequest;
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

        $category = UserModerationCategory::where('key', $validated['key'])->first();
        if ($category) {
            return response()->json([
                    'message' => 'Category key already exists.',
                    'error' => 'category_already_exists',
                ],
                JsonResponse::HTTP_NOT_ACCEPTABLE
            );
        }

        $category = new UserModerationCategory;
        $category->key          = $validated['key'];
        $category->level        = $validated['level'];
        $category->position     = $validated['position'];
        $category->title        = $validated['title'];
        $category->description  = $validated['description'];
        $category->save();

        $response = [
            'id'            => $category->id,
            'key'           => $category->key,
            'level'         => $category->level,
            'position'      => $category->position,
            'title'         => $category->title,
            'description'   => $category->description,
            'created_at'    => $category->created_at->format('Y-m-d H:i:s'),
            'updated_at'    => $category->updated_at->format('Y-m-d H:i:s'),
        ];

        return response()->json($response, JsonResponse::HTTP_CREATED);
    }

    /**
     * GET /api/v1/moderations/categories/{id}
     */
    public function read(int $id): JsonResponse
    {
        $category = UserModerationCategory::where('id', $id)->first();
        if (! $category) {
            return response()->json([
                    'message' => 'Category not found.',
                    'error' => 'category_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $response = [
            'id'            => $category->id,
            'key'           => $category->key,
            'level'         => $category->level,
            'position'      => $category->position,
            'title'         => $category->title,
            'description'   => $category->description,
            'created_at'    => $category->created_at->format('Y-m-d H:i:s'),
            'updated_at'    => $category->updated_at->format('Y-m-d H:i:s'),
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * PATCH /api/v1/moderations/categories/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $formRequest = new UserModerationCategoryUpdateRequest;
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

        $category = UserModerationCategory::where('id', $id)->first();
        if (! $category) {
            return response()->json([
                    'message' => 'Category not found.',
                    'error' => 'category_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        ! isset($validated['key']) ? : $category->key = $validated['key'];
        ! isset($validated['level']) ? : $category->level = $validated['level'];
        ! isset($validated['position']) ? : $category->position = $validated['position'];
        ! isset($validated['title']) ? : $category->title = $validated['title'];
        ! isset($validated['description']) ? : $category->description = $validated['description'];
        $category->save();

        $response = [
            'id'            => $category->id,
            'key'           => $category->key,
            'level'         => $category->level,
            'position'      => $category->position,
            'title'         => $category->title,
            'description'   => $category->description,
            'created_at'    => $category->created_at->format('Y-m-d H:i:s'),
            'updated_at'    => $category->updated_at->format('Y-m-d H:i:s'),
        ];

        return response()->json($response, JsonResponse::HTTP_ACCEPTED);
    }

    /**
     * DELETE /api/v1/moderations/categories/{id}
     */
    public function delete(int $id): JsonResponse
    {
        $category = UserModeration::query()
            ->with(['moderations'])
            ->where('id', $id)
            ->first();

        if (! $category) {
            return response()->json([
                    'message' => 'Category not found.',
                    'error' => 'category_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        if ($category->moderations->count() >= 1) {
            return response()->json([
                    'message' => 'Category has related moderations.',
                    'error' => 'category_has_related_moderations',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $legacyCategory = $category;

        $category->delete();

        $response = [
            'message' => 'Moderation Category -'.$legacyCategory->key.'- has been successfully deleted.',
            'id' => $legacyCategory->id,
            'key' => $legacyCategory->key,
        ];

        return response()->json($response, JsonResponse::HTTP_ACCEPTED);
    }
}
