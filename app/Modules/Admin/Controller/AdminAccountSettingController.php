<?php

namespace App\Modules\Admin\Controller;

use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminAccountSettingController
{
    /**
     * GET /api/v1/account/settings
     */
    public function read(): JsonResponse
    {
        /** @var Illuminate\Auth\AuthManager $user */
        $user = Auth::user();
        $user->load([
            'admin',
            'adminProfile',
            'avatar',
            'admin.accessLogs' => function ($q) {
                $q->select([
                    'admins_access_logs.id',        // primary key
                    'admins_access_logs.user_id',   // FK used by the relation
                    'admins_access_logs.ip_address',
                    'admins_access_logs.user_agent',
                    'admins_access_logs.created_at',
                    'admins_access_logs.expires_at',
                ])
                    ->orderBy('admins_access_logs.created_at', 'desc')
                    ->limit(1);
            },
        ]);

        $admin = $user->admin;
        $profile = $user->adminProfile;
        $region = $admin?->region;
        $continent = $region?->continent;

        return response()->json(
            [
                'email' => $user->email,
                'uid' => $admin->uid,
                'nickname' => $profile->nickname,
                'avatar' => $user->avatar?->url ?? null,
                'account_created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i:s'),
                'password_changed_at' => $user->password_changed_at?->format('Y-m-d H:i:s'),
                'geo' => [
                    'continent_id' => $continent->id ?? null,
                    'continent_name' => $continent->name ?? null,
                    'region_id' => $region->id ?? null,
                    'region_name' => $region->name ?? null,
                ],
                'access_logs' => $user->admin->accessLogs?->map(fn ($log) => [
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'created_at' => $log->created_at?->format('Y-m-d H:i:s'),
                    'expires_at' => $log->expires_at?->format('Y-m-d H:i:s'),
                ]),
            ],
            JsonResponse::HTTP_OK
        );
    }

    /**
     * PATCH /api/v1/account/settings
     */
    public function update(): JsonResponse
    {
        return response()->json(
            [
                'message' => 'in development',
            ],
            JsonResponse::HTTP_OK
        );
    }
}
