<?php

namespace App\Domain\User\Controller;

use App\Domain\Member\Models\Member;
use Illuminate\Http\Request;
use App\Support\Paginate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\User\Requests\UserMemberRequest;

class UserMemberController extends Controller
{
    /**
     * Feed Post Filters
     */
    public static function filters(): array
    {
        return [
            'sorting' => [
                'recent' => 'Recent',
                'oldest' => 'Oldest',
            ],
        ];
    }

    /**
     * /api/v1/users/members
     */
    public function listing(Request $request): JsonResponse
    {
        $filters = [];

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
        $validated = $validator->validated();

        $params = new \stdClass;
        ! isset($validated['sort-by']) ? : $params->sort_by = $validated['sort-by'];

        $query = Member::query()
            ->with([
                'user',
                'profile',
                'avatar',
                'continent',
                'region',
            ]);

        $listing = Paginate::listing($query->count(), $filters);

        $usersMember = $query->skip(($listing->page - 1) * $listing->limit)
            ->take($listing->limit)
            ->get();

        $response = [
            'filters' => $this->filters(),
            'listing' => $listing,
            'result'  => $usersMember,
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * /api/v1/users/members/{uid}
     */
    public function baseData(int $uid): JsonResponse
    {
        $member = Member::query()
            ->with([
                'profile',
                'avatar',
                'accessLogs' => function ($query) {
                    $query->latest()->limit(1);
                }
            ])
            ->where('uid', $uid)
            ->first();

        $response = [
            'email'     => $member->user->email,
            'uid'       => $member->uid,
            'nickname'  => $member->profile->nickname,
            'avatar'    => $member->avatar?->url ?? null,
            'since'     => $member->created_at->format('Y-m-d H:i:s') ?? null,
            'last-seen' => $member->accessLogs?->updated_at->format('Y-m-d H:i:s') ?? null,
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * /api/v1/users/members/{uid}/profile
     */
    public function profile(int $uid): JsonResponse
    {
        $member = Member::query()
            ->with([
                'profile',
                'avatar',
            ])
            ->where('uid', $uid)
            ->first();

        $response = [
            'email'     => $member->user->email,
            'uid'       => $member->uid,
            'nickname'  => $member->profile->nickname,
            'avatar'    => $member->avatar?->url ?? null,
            'since'     => $member->created_at->format('Y-m-d H:i:s') ?? null,
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
