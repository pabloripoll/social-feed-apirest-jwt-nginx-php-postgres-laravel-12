<?php

namespace App\Domain\Member\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;

class MemberProfileController
{
    /**
     * GET /api/v1/account/profile
     */
    public function readProfile(Request $request): JsonResponse
    {
        /** @var Illuminate\Auth\AuthManager $user */
        $user = Auth::user();
        $user->load(['member', 'memberProfile']);
        $userAccount = $user->member;
        $userProfile = $user->memberProfile;

        return response()->json(
            [
                'email' => $user->email,
                'uid' => $userAccount->uid,
                'nickname' => $userProfile->nickname,
                'avatar' => $user->avatar,
                'region' => [
                    'region_id' => $user->region_id,
                ],
            ],
            JsonResponse::HTTP_OK
        );
    }

    /**
     * PATCH /api/v1/account/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
