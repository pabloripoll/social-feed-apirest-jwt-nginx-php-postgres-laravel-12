<?php

namespace App\Domain\Member\Controller;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MemberAccountController
{
    /**
     * GET /api/v1/account/settings
     */
    public function readSettings(): JsonResponse
    {
        /** @var Illuminate\Auth\AuthManager $user */
        $user = Auth::user();
        $user->load([
            'member',
            'memberProfile',
            'member.accessLogs' => function ($q) {
                $q->select([
                    'members_access_logs.id',        // primary key
                    'members_access_logs.user_id',   // FK used by the relation
                    'members_access_logs.ip_address',
                    'members_access_logs.user_agent',
                    'members_access_logs.created_at',
                    'members_access_logs.expires_at',
                ])
                ->orderBy('members_access_logs.created_at', 'desc')
                ->limit(1);
            },
        ]);

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
                'access_logs' => $user->member->accessLogs?->map(fn($log) => [
                    'ip_address'  => $log->ip_address,
                    'user_agent'  => $log->user_agent,
                    'created_at'  => $log->created_at?->format('Y-m-d H:i:s'),
                    'expires_at'  => $log->expires_at?->format('Y-m-d H:i:s'),
                ])
            ],
            JsonResponse::HTTP_OK
        );
    }
}
