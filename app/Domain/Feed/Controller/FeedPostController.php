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
use App\Domain\Feed\Repository\FeedPostRepository;
use App\Domain\Feed\Service\FeedPostService;

class FeedPostController
{
    /**
     * GET /api/v1/feed/posts
     */
    public function posts(Request $request): JsonResponse
    {
        /** @var \App\Domain\User\Models\User|null $user */
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

        $filters = new \stdClass;
        ! isset($validated['category']) ? : $filters->category = $validated['category'];
        ! isset($validated['sort-by']) ? : $filters->sort_by = $validated['sort-by'];

        $query = FeedPostRepository::listing($filters, $user);

        $listing = Paginate::listing($query->count(), $filters);

        $posts = Paginate::result($query, $listing);

        $response = [
            'filters' => FeedPostService::filters(),
            'listing' => $listing,
            'result' => FeedPostResource::collection($posts),
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/feed/posts/following
     */
    public function followingMembersPosts(Request $request): JsonResponse
    {
        /** @var \App\Domain\User\Models\User|null $user */
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

        $filters = new \stdClass;
        $filters->following = true;
        ! isset($validated['category']) ? : $filters->category = $validated['category'];
        ! isset($validated['sort-by']) ? : $filters->sort_by = $validated['sort-by'];

        $query = FeedPostRepository::listing($filters, $user);

        $resultCount = $query->count();

        $listing = Paginate::listing($resultCount, $filters);

        $posts = Paginate::result($query, $listing);

        $response = [
            'filters' => FeedPostService::filters(),
            'listing' => $listing,
            'result'  => FeedPostResource::collection($posts),
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
