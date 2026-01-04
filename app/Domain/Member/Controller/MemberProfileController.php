<?php

namespace App\Domain\Member\Controller;

use App\Domain\Member\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Support\Paginate;
use Illuminate\Support\Facades\Validator;
use App\Domain\Feed\Models\FeedPost;
use App\Domain\Feed\Service\FeedPostService;
use App\Domain\Feed\Requests\FeedPostRequest;
use App\Domain\Feed\Resources\FeedPostResource;

class MemberProfileController
{
    /**
     * GET /api/v1/member/{member_uid}/profile
     */
    public function read(Request $request, int $member_uid): JsonResponse
    {
        $member = Member::query()
            ->with(['user', 'profile', 'avatar'])
            ->where('uid', $member_uid)
            ->first();
        if (! $member) {
            return response()->json(
                [
                    'message' => 'Member '.$member_uid.' not found.',
                    'error' => 'member_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $user = $member->user;
        $profile = $member->profile;
        $region = $member->region;
        $continent = $region->continent;

        return response()->json(
            [
                'uid' => $member->uid,
                'nickname' => $profile->nickname,
                'avatar' => $member->avatar ?? null,
                'member_since' => $user->created_at->format('Y-m-d H:i:s'),
                'geo' => [
                    'continent_id' => $continent->id ?? null,
                    'continent_name' => $continent->name ?? null,
                    'region_id' => $region->id ?? null,
                    'region_name' => $region->name ?? null,
                ],
                'feed' => [
                    'posts_count' => $member->feed_posts_count,
                    'posts' => '/api/v1/member/'.$member->uid.'/feed/posts'
                ],
            ],
            JsonResponse::HTTP_OK
        );
    }

    /**
     * GET /api/v1/member/{member_uid}/feed/posts
     */
    public function feedPosts(Request $request, int $member_uid): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();

        $formRequest = new FeedPostRequest;
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

        $member = Member::query()
            ->with(['user', 'profile'])
            ->where('uid', $member_uid)
            ->first();
        if (! $member) {
            return response()->json(
                [
                    'message' => 'Member '.$member_uid.' not found.',
                    'error' => 'member_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $query = FeedPost::query()
            ->with(['user', 'member', 'category', 'continent', 'region', 'media'])
            ->where('user_id', $member->user->id)
            ->where('is_active', true);

        $filters = [];

        if (isset($validated['category'])) {
            $filters['category'] = $validated['category'];

            $query->whereHas('category', function ($q) use ($validated) {
                $q->where('key', $validated['category']);
            });
        }

        $sortReference = 'created_at';
        $sortDirection = 'desc';
        if (isset($validated['sort-by'])) {
            $ref = $validated['sort-by'];
            $filters['sort-by'] = $validated['sort-by'];

            $sortReference = $ref != 'thumbs-up' ? $sortReference : 'thumbs_up_count';
            $sortReference = $ref != 'thumbs-down' ? $sortReference : 'thumbs_down_count';

            $sortDirection = $ref == 'oldest' ? 'asc' : $sortDirection;
        }
        $query->orderBy($sortReference, $sortDirection);

        $listing = Paginate::listing($query->count(), $filters);

        $posts = Paginate::result($query, $listing);

        $response = [
            'filters' => FeedPostService::filters(),
            'listing' => $listing,
            'result' => FeedPostResource::collection($posts),
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
