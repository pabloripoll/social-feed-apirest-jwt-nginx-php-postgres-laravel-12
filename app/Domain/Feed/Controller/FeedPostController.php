<?php

namespace App\Domain\Feed\Controller;

use App\Domain\Feed\Models\FeedPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\User\Models\User;
use App\Domain\Member\Service\MemberService;

class FeedPostController
{
    /**
     * POST /api/v1/account/feed/posts
     */
    public function createPost(): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();
        $user->load(['member']);

        $memberStatus = (new MemberService)->checkAccess($user);
        if (! $memberStatus) {
            return response()->json([
                    'message' => $memberStatus->message,
                    'error' => $memberStatus->error,
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        FeedPost::query()
            ->where('user_id', $user->id)
            ->where('is_sketch', true)
            ->delete();

        $post = new FeedPost;
        $post->user_id = $user->id;
        $post->region_id = $user->member->region_id;
        $post->category_id = 1;
        $post->is_sketch = true;
        $post->save();

        $response = [
            'message' => 'post sketch has been succefully created.',
            'post_uid' => $post->uid,
        ];

        return response()->json($response, JsonResponse::HTTP_CREATED);
    }

    /**
     * PUT /api/v1/account/feed/posts/{post_uid}
     */
    public function broadcastPost(Request $request, int $post_uid): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/account/feed/posts/{post_uid}
     */
    public function readPost(Request $request, int $post_uid): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * PATCH /api/v1/account/feed/posts/{post_uid}
     */
    public function updatePost(Request $request, int $post_uid): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * DELETE /api/v1/account/feed/posts/{post_uid}
     */
    public function deletePost(Request $request, int $post_uid): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
