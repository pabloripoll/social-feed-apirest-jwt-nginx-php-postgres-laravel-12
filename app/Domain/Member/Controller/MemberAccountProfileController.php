<?php

namespace App\Domain\Member\Controller;

use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;

class MemberAccountProfileController
{
    /**
     * GET /api/v1/account/profile
     */
    public function read(): JsonResponse
    {
        /** @var Illuminate\Auth\AuthManager $user */
        $user = Auth::user();
        $user->load(['member', 'memberProfile', 'avatars']);

        $member = $user->member;
        $profile = $user->memberProfile;
        $region = $member?->region;
        $continent = $region?->continent;
        $avatars = $user->avatars ?? [];

        return response()->json(
            [
                'email' => $user->email,
                'uid' => $member->uid,
                'nickname' => $profile->nickname,
                'account_created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i:s'),
                'password_changed_at' => $user->password_changed_at?->format('Y-m-d H:i:s'),
                'geo' => [
                    'continent_id' => $continent->id ?? null,
                    'continent_name' => $continent->name ?? null,
                    'region_id' => $region->id ?? null,
                    'region_name' => $region->name ?? null,
                ],
                'avatars' => $avatars,
            ],
            JsonResponse::HTTP_OK
        );
    }

    /**
     * PATCH /api/v1/account/profile
     */
    public function update(): JsonResponse
    {
        return response()->json(
            [
                'message' => 'in development'
            ],
            JsonResponse::HTTP_OK
        );
    }
}
