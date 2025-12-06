<?php

namespace App\Domain\Feed\Controller;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class FeedThumbController
{
    /**
     * POST /api/v1/feed/posts/{post_id}/thumbs/up
     */
    public function createThumbUp(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * DELETE /api/v1/feed/posts/{post_id}/thumbs/up
     */
    public function deleteThumbUp(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * POST /api/v1/feed/posts/{post_id}/thumbs/up
     */
    public function createThumbDown(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * DELETE /api/v1/feed/posts/{post_id}/thumbs/up
     */
    public function deleteThumbDown(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
