<?php

namespace App\Domain\Feed\Controller;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class FeedPostFavouriteController
{
    /**
     * POST /api/v1/feed/posts/{post_id}/favourites
     */
    public function addFavourites(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * DELETE /api/v1/feed/posts/{post_id}/favourites
     */
    public function deleteFavourite(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
