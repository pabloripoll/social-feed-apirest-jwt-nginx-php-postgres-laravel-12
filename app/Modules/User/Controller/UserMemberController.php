<?php

namespace App\Modules\User\Controller;

use App\Modules\Member\Models\Member;
use App\Modules\User\Requests\UserMemberRequest;
use App\Modules\User\Resources\UserMemberResource;
use App\Modules\User\Service\UserMemberService;
use App\Http\Controllers\Controller;
use App\Support\Paginate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserMemberController extends Controller
{
    /**
     * /api/v1/users/members
     */
    public function listing(Request $request): JsonResponse
    {
        $formRequest = new UserMemberRequest;
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

        $query = Member::query()
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

        $members = Paginate::result($query, $listing);

        $response = [
            'filters' => UserMemberService::filters(),
            'listing' => $listing,
            'result' => UserMemberResource::collection($members),
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/users/members/{id}
     */
    public function read(int $id): JsonResponse
    {
        $member = Member::query()
            ->with([
                'user',
                'profile',
                'latestAccessLog',
            ])
            ->where('user_id', $id)
            ->first();

        if (! $member) {
            return response()->json([
                'message' => 'User not found.',
                'error' => 'user_not_found',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $response = [
            'id' => $member->user_id,
            'uid' => $member->uid,
            'email' => $member->user->email,
            'is_active' => $member->is_active,
            'is_banned' => $member->is_banned,
            'nickname' => $member->profile->nickname,
            'created_at' => $member->user->created_at->format('Y-m-d H:i:s') ?? null,
            'last_access' => $member->accessLogs ?? null,
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/users/members/{id}/profile
     */
    public function profile(int $id): JsonResponse
    {
        $member = Member::query()
            ->with([
                'user',
                'profile',
                'avatar',
                'continent',
                'region',
            ])
            ->where('user_id', $id)
            ->first();

        if (! $member) {
            return response()->json([
                'message' => 'User was not found.',
                'error' => 'user_not_found',
            ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $response = new UserMemberResource($member);

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
