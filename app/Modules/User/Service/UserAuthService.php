<?php

namespace App\Modules\User\Service;

use App\Modules\Admin\Models\AdminAccessLog;
use App\Modules\Member\Models\MemberAccessLog;
use App\Modules\User\Models\Role;
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
     *
     * This service method does not evaluate if token is expired for giving the chance to be refreshed
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
                $accessLog = AdminAccessLog::where('token', $token)->first();
            }

            if ($payload['role'] == Role::MEMBER) {
                $accessLog = MemberAccessLog::where('token', $token)->first();
            }

            if (! $accessLog || $accessLog->is_terminated) {
                return null;
            }

            return $accessLog;

        } catch (JWTException $e) {
            return null;
        }
    }
}
