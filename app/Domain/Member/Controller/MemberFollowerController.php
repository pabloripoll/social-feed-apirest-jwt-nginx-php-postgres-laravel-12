<?php

namespace App\Domain\Member\Controller;

use App\Domain\Member\Models\Member;
use App\Domain\Member\Models\MemberFollower;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;

class MemberFollowerController
{
    /**
     * POST /api/v1/members/{member_uid}/follow
     */
    public function follow(int $member_uid): JsonResponse
    {
        /** @var Illuminate\Auth\AuthManager $user */
        $user = Auth::user();
        $user->load(['member', 'memberProfile', 'memberAvatar']);

        if ($member_uid == $user->member->uid) {
            return response()->json([
                    'message' => 'Member cannot follow itself.',
                    'error' => 'wrong_follow',
                ],
                JsonResponse::HTTP_NOT_ACCEPTABLE
            );
        }

        $member = Member::query()
            ->with(['user', 'profile', 'avatar'])
            ->where('uid', $member_uid)
            ->first();
        if (! $member) {
            return response()->json([
                    'message' => 'Member not found.',
                    'error' => 'member_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $follower = MemberFollower::query()
            ->where('user_id', $user->id)
            ->where('following_user_id', $member->user->id)
            ->first();
        if ($follower) {
            return response()->json([
                    'message' => 'You are already following @'.$member->profile->nickname.'.',
                    'error' => 'following_already_exists',
                ],
                JsonResponse::HTTP_OK
            );
        }

        $follower = new MemberFollower;
        $follower->user_id = $user->id;
        $follower->following_user_id = $member->user->id;
        $follower->save();

        $userFollower = [
            'uid' => $user->member->uid,
            'nickname' => $user->memberProfile->nickname,
            'avatar' => $user?->memberAvatar->url ?? null,
        ];

        $userFollowing = [
            'uid' => $member->uid,
            'nickname' => $member->profile->nickname,
            'avatar' => $member?->avatar->url ?? null,
        ];

        // Dependencies

        $user->member->following_count = $user->member->following_count + 1;
        $user->member->save();

        $member->followers_count = $member->followers_count + 1;
        $member->save();

        return response()->json(
            [
                'message' => 'Following @'.$userFollowing['nickname'].' suscription successfully created.',
                'follower' => $userFollower,
                'following' => $userFollowing,
            ],
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * POST /api/v1/members/{member_uid}/unfollow
     */
    public function unfollow(int $member_uid): JsonResponse
    {
        /** @var Illuminate\Auth\AuthManager $user */
        $user = Auth::user();
        $user->load(['member', 'memberProfile', 'memberAvatar']);

        if ($member_uid == $user->member->uid) {
            return response()->json([
                    'message' => 'Member cannot unfollow itself.',
                    'error' => 'wrong_unfollow',
                ],
                JsonResponse::HTTP_NOT_ACCEPTABLE
            );
        }

        $member = Member::query()
            ->with(['user', 'profile', 'avatar'])
            ->where('uid', $member_uid)
            ->first();
        if (! $member) {
            return response()->json([
                    'message' => 'Member not found.',
                    'error' => 'member_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $follower = MemberFollower::query()
            ->where('user_id', $user->id)
            ->where('following_user_id', $member->user->id)
            ->first();
        if (! $follower) {
            return response()->json([
                    'message' => 'You are not following @'.$member->profile->nickname.'.',
                    'error' => 'not_following',
                ],
                JsonResponse::HTTP_OK
            );
        }

        $follower->delete();

        $userFollower = [
            'uid' => $user->member->uid,
            'nickname' => $user->memberProfile->nickname,
            'avatar' => $user?->memberAvatar->url ?? null,
        ];

        $userFollowing = [
            'uid' => $member->uid,
            'nickname' => $member->profile->nickname,
            'avatar' => $member?->avatar->url ?? null,
        ];

        // Dependencies

        $user->member->following_count = max(0, $user->member->following_count - 1);
        $user->member->save();

        $member->followers_count = max(0, $member->followers_count - 1);
        $member->save();

        return response()->json(
            [
                'message' => 'Following @'.$userFollowing['nickname'].' suscription successfully deleted.',
                'follower' => $userFollower,
                'following' => $userFollowing,
            ],
            JsonResponse::HTTP_ACCEPTED
        );
    }
}
