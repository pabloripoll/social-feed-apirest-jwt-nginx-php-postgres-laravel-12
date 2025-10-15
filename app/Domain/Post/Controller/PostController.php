<?php

namespace App\Domain\Post\Controller;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class PostController
{
    /**
     * POST /api/v1/feed/posts
     */
    public function createPost(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * PUT /api/v1/feed/posts/{post_id}
     */
    public function setPost(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * PATCH /api/v1/feed/posts/{post_id}
     */
    public function updatePost(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * DELETE /api/v1/feed/posts/{post_id}
     */
    public function deletePost(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * POST /api/v1/feed/posts/{post_id}/media
     */
    public function uploadPostMedia(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * DELETE /api/v1/feed/posts/{post_id}/media
     */
    public function deletePostMedia(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * POST /api/v1/feed/posts/{post_id}/report
     */
    public function createPostReport(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
