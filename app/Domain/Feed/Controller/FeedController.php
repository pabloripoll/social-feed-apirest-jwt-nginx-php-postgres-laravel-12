<?php

namespace App\Domain\Post\Controller;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class FeedController
{
    /**
     * GET /api/v1/feed
     */
    public function listPosts(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/feed/reports
     */
    public function listReports(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/feed/categories
     */
    public function listCategories(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/feed/posts/{post_id}
     */
    public function readPost(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/feed/posts/{post_id}/votes
     */
    public function listPostVotes(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
