<?php

namespace App\Modules\Feed\Controller;

use App\Modules\Feed\Models\FeedPost;
use App\Modules\Feed\Repository\FeedPostRepository;
use App\Modules\Feed\Requests\FeedPostRequest;
use App\Modules\Feed\Resources\FeedPostResource;
use App\Modules\Feed\Service\FeedPostService;
use App\Support\Paginate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;

class FeedPostController
{
    /**
     * GET /api/v1/feed/posts
     */
    public function posts(Request $request): JsonResponse
    {
        /** @var \App\Modules\User\Models\User|null $user */
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

        $filters = (object) $validator->validated();

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
        /** @var \App\Modules\User\Models\User|null $user */
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

        $filters = (object) $validator->validated();
        $filters->following = true;

        $query = FeedPostRepository::listing($filters, $user);

        $resultCount = $query->count();

        $listing = Paginate::listing($resultCount, $filters);

        $posts = Paginate::result($query, $listing);

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
