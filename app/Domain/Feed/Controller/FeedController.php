<?php

namespace App\Domain\Feed\Controller;

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
}
