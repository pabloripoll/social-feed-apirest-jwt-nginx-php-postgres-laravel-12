<?php

namespace App\Domain\Feed\Controller;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\User\Models\User;
use App\Domain\Feed\Models\FeedMultimedia;

class FeedMediaController
{
    /**
     * POST
     */
    public function uploadMedia(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * DELETE
     */
    public function deleteMedia(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
