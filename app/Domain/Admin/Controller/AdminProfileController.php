<?php

namespace App\Domain\Admin\Controller;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminProfileController
{
    /**
     * GET /api/v1/admin/members
     */
    public function listSections(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/admin/members/{user_uid}/profile
     */
    public function readProfile(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/admin/members/{user_uid}/posts
     */
    public function listingPosts(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
