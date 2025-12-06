<?php

namespace App\Domain\Member\Controller;

use App\Domain\Member\Models\Member;
use App\Domain\User\Models\User;
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

        $member = $user->member;
        $profile = $user->memberProfile;
        $region = $member?->region;
        $continent = $region?->continent;

        return response()->json(
            [
                'email' => $user->email,
                'uid' => $member->uid,
                'nickname' => $profile->nickname,
                'avatar' => $member->avatar,
                'account_created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i:s'),
                'password_changed_at' => $user->password_changed_at?->format('Y-m-d H:i:s'),
                'geo' => [
                    'continent_id' => $continent->id ?? null,
                    'continent_name' => $continent->name ?? null,
                    'region_id' => $region->id ?? null,
                    'region_name' => $region->name ?? null,
                ],
            ],
            JsonResponse::HTTP_OK
        );
    }

    /**
     * GET /api/v1/member/{member_uid}/profile
     */
    public function readMemberProfile(Request $request, int $member_uid): JsonResponse
    {
        $member = Member::query()
            ->with(['user', 'profile'])
            ->where('uid', $member_uid)
            ->first();

        if (! $member) {
            return response()->json(
                [
                    'message' => 'Member ' . $member_uid . ' not found.',
                    'error' => 'member_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $user = $member->user;
        $profile = $member->profile;
        $region = $member->region;
        $continent = $region->continent;

        return response()->json(
            [
                'uid' => $member->uid,
                'nickname' => $profile->nickname,
                'avatar' => $member->avatar,
                'member_since' => $user->created_at->format('Y-m-d H:i:s'),
                'geo' => [
                    'continent_id' => $continent->id ?? null,
                    'continent_name' => $continent->name ?? null,
                    'region_id' => $region->id ?? null,
                    'region_name' => $region->name ?? null,
                ],
                'feed' => [
                    'posts_count' => $member->feed_posts_count
                ]
            ],
            JsonResponse::HTTP_OK
        );
    }
}
