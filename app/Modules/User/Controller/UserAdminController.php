<?php

namespace App\Modules\User\Controller;

use App\Modules\Admin\Models\Admin;
use App\Modules\User\Requests\UserAdminRequest;
use App\Modules\User\Resources\UserAdminResource;
use App\Modules\User\Service\UserAdminService;
use App\Http\Controllers\Controller;
use App\Support\Paginate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserAdminController extends Controller
{
    /**
     * /api/v1/users/admins
     */
    public function listing(Request $request): JsonResponse
    {
        $formRequest = new UserAdminRequest;
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
        $filters = (object) $validator->validated();

        $query = Admin::query()
            ->with([
                'user',
                'profile',
                'avatar',
                'continent',
                'region',
            ]);

        if (isset($filters->sort_by)) {
            $query = $filters->sort_by == 'oldest' ? $query->oldest() : $query->latest();
        }

        $listing = Paginate::listing($query->count(), $filters);

        $admins = Paginate::result($query, $listing);

        $response = [
            'filters' => UserAdminService::filters(),
            'listing' => $listing,
            'result' => UserAdminResource::collection($admins),
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/users/admins/{id}
     */
    public function read(int $id): JsonResponse
    {
        $admin = Admin::query()
            ->with([
                'user',
                'profile',
                'latestAccessLog',
            ])
            ->where('user_id', $id)
            ->first();

        if (! $admin) {
            return response()->json([
                'message' => 'User not found.',
                'error' => 'user_not_found',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $response = [
            'id' => $admin->user_id,
            'uid' => $admin->uid,
            'email' => $admin->user->email,
            'is_active' => $admin->is_active,
            'is_banned' => $admin->is_banned,
            'nickname' => $admin->profile->nickname,
            'created_at' => $admin->user->created_at->format('Y-m-d H:i:s') ?? null,
            'last_access' => $admin->accessLogs ?? null,
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/users/admins/{id}/profile
     */
    public function profile(int $id): JsonResponse
    {
        $admin = Admin::query()
            ->with([
                'user',
                'profile',
                'avatar',
                'continent',
                'region',
            ])
            ->where('user_id', $id)
            ->first();

        if (! $admin) {
            return response()->json([
                'message' => 'User not found.',
                'error' => 'user_not_found',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $response = new UserAdminResource($admin);

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
