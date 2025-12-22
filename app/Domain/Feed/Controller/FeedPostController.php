<?php

namespace App\Domain\Feed\Controller;

use App\Support\Paginate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\Feed\Models\FeedPost;
use App\Domain\Feed\Requests\FeedPostRequest;
use App\Domain\Feed\Resources\FeedPostResource;
use App\Domain\Feed\Service\FeedPostService;
use Illuminate\Support\Facades\DB;

class FeedPostController
{
    /**
     * GET /api/v1/feed/posts
     */
    public function posts(Request $request): JsonResponse
    {
        /** @var \App\Domain\User\Models\User|null $user */
        $user = Auth::user();

        $filters = [];

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

        $query = FeedPost::query()
            ->select('feed_posts.*') // make sure the model has all its columns selected
            ->with(['user', 'member', 'avatar', 'category', 'continent', 'region', 'media'])
            ->where('is_active', true);

        // If user is authenticated, add boolean flags via subqueries
        if ($user) {
            $authUserId = (int) $user->id;

            // whether auth user has thumbed up this post
            $query->addSelect(DB::raw("EXISTS (
                select 1 from feed_posts_thumbs
                where feed_posts_thumbs.post_id = feed_posts.id
                and feed_posts_thumbs.user_id = {$authUserId}
                and feed_posts_thumbs.up = true
            ) as is_thumb_up_by_me"));

            // whether auth user has thumbed down this post
            $query->addSelect(DB::raw("EXISTS (
                select 1 from feed_posts_thumbs
                where feed_posts_thumbs.post_id = feed_posts.id
                and feed_posts_thumbs.user_id = {$authUserId}
                and feed_posts_thumbs.down = true
            ) as is_thumb_down_by_me"));

            // whether auth user follows the post owner (auth -> following -> post owner)
            $query->addSelect(DB::raw("EXISTS (
                select 1 from members_following
                where members_following.user_id = {$authUserId}
                and members_following.following_user_id = feed_posts.user_id
            ) as is_post_from_following"));

            // whether post owner follows the auth user (post owner -> following -> auth)
            $query->addSelect(DB::raw("EXISTS (
                select 1 from members_following
                where members_following.user_id = feed_posts.user_id
                and members_following.following_user_id = {$authUserId}
            ) as is_post_from_follower"));
        } else {
            // when not authenticated ensure flags exist and are false (optional)
            $query->addSelect(DB::raw('false as is_thumb_up_by_me'));
            $query->addSelect(DB::raw('false as is_thumb_down_by_me'));
            $query->addSelect(DB::raw('false as is_post_from_following'));
            $query->addSelect(DB::raw('false as is_post_from_follower'));
        }

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

        // Pagination
        $listing = Paginate::listing($query->count(), $filters);

        $posts = $query->paginate($listing->limit, ['*'], 'page', $listing->page);

        $response = [
            'filters' => FeedPostService::filters(),
            'listing' => $listing,
            'result' => FeedPostResource::collection($posts),
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/feed/posts/{uid}
     */
    public function readPost(int $uid): JsonResponse
    {
        $post = FeedPost::query()
            ->with(['user', 'member', 'avatar', 'category', 'continent', 'region', 'media'])
            ->where('uid', $uid)
            ->where('is_active', true)
            ->first();
        if (! $post) {
            return response()->json([
                    'message' => 'No feed sketch post found.',
                    'error' => 'post_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        if ($post->is_banned) {
            return response()->json([
                    'message' => 'Feed post cannot be edited.',
                    'error' => 'post_banned',
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $response = [
            'message' => 'Feed post has successfully read.',
            'post' => new FeedPostResource($post),
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
