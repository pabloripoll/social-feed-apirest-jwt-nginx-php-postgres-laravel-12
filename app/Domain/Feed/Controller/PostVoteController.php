<?php

namespace App\Domain\Post\Controller;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class PostVoteController
{
    /**
     * POST /api/v1/feed/posts/{post_id}/votes/up
     */
    public function createVoteUp(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * DELETE /api/v1/feed/posts/{post_id}/votes/up
     */
    public function deleteVoteUp(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * POST /api/v1/feed/posts/{post_id}/votes/up
     */
    public function createVoteDown(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * DELETE /api/v1/feed/posts/{post_id}/votes/up
     */
    public function deleteVoteDown(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
