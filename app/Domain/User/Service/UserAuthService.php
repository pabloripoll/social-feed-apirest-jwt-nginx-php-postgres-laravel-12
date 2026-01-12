<?php

namespace App\Domain\User\Service;

use App\Domain\Admin\Models\AdminAccessLog;
use App\Domain\Member\Models\MemberAccessLog;
use App\Domain\User\Models\Role;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserAuthService
{
    /**
     * JWT access expiration, smaller than JWT TTL config
     */
    public $jwtTime = 60;

    /**
     * Check JWT
     */
    public function checkToken(): AdminAccessLog|MemberAccessLog|null
    {
        $token = JWTAuth::getToken();
        if (! $token) {
            return null;
        }

        try {
            $payload = JWTAuth::decode($token);

            if ($payload['role'] == Role::ADMIN) {
                $accessLog = AdminAccessLog::where('token', $token)->latest()->first();
            }

            if ($payload['role'] == Role::MEMBER) {
                $accessLog = MemberAccessLog::where('token', $token)->latest()->first();
            }

            if (! $accessLog || $accessLog->is_terminated || $accessLog->is_expired) {
                return null;
            }

            return $accessLog;

        } catch (JWTException $e) {
            return null;
        }
    }
}
