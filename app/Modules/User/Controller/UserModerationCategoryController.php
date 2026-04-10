<?php

namespace App\Modules\User\Controller;

use App\Modules\User\Models\UserModerationCategory;
use App\Modules\User\Requests\UserModerationCategoryCreateRequest;
use App\Modules\User\Requests\UserModerationCategoryUpdateRequest;
use App\Modules\User\Resources\UserModerationCategoryResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserModerationCategoryController extends Controller
{
    /**
     * GET /api/v1/moderations/categories
     */
    public function listing(Request $request): JsonResponse
    {
        $categories = UserModerationCategory::query()
            ->orderBy('position', 'asc')
            ->get();

        $response = UserModerationCategoryResource::collection($categories);

        return response()->json($response, JsonResponse::HTTP_OK);
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
        $category->key = $validated['key'];
        $category->level = $validated['level'];
        $category->position = $validated['position'];
        $category->title = $validated['title'];
        $category->description = $validated['description'];
        $category->save();

        $response = new UserModerationCategoryResource($category);

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

        $response = new UserModerationCategoryResource($category);

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

        ! isset($validated['key']) ?: $category->key = $validated['key'];
        ! isset($validated['level']) ?: $category->level = $validated['level'];
        ! isset($validated['position']) ?: $category->position = $validated['position'];
        ! isset($validated['title']) ?: $category->title = $validated['title'];
        ! isset($validated['description']) ?: $category->description = $validated['description'];
        $category->save();

        $response = new UserModerationCategoryResource($category);

        return response()->json($response, JsonResponse::HTTP_ACCEPTED);
    }

    /**
     * DELETE /api/v1/moderations/categories/{id}
     */
    public function delete(int $id): JsonResponse
    {
        $category = UserModerationCategory::query()
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

        if ($category->moderations()->exists()) {
            return response()->json([
                'message' => 'Category has related moderations.',
                'error' => 'category_has_related_moderations',
            ],
                JsonResponse::HTTP_CONFLICT
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
