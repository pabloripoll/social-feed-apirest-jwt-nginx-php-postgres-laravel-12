<?php

namespace App\Http\Middleware;

use App\Domain\Admin\Models\AdminAccessLog;
use App\Domain\Member\Models\MemberAccessLog;
use Closure;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        $token = JWTAuth::getToken();
        if (! $token) {
            return response()->json(['message' => 'Token not provided.', 'error' => 'token_not_provided'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        try {
            $jwtString = (string) $token;
            $jwtPayload = JWTAuth::payload($token);

        } catch (JWTException $e) {
            return response()->json(['message' => 'Token invalid or expired.', 'error' => 'token_invalid'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $role = $jwtPayload->get('role');
        if ($role == 'admin') {
            $accessToken = AdminAccessLog::where('token', $jwtString)->first();
        } else {
            $accessToken = MemberAccessLog::where('token', $jwtString)->first();
        }

        if (! $accessToken) {
            return response()->json(['message' => 'Token not registered.', 'error' => 'token_not_found'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        if ($accessToken->is_terminated) {
            return response()->json(['message' => 'Token is terminated.', 'error' => 'token_terminated'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        if ($accessToken->is_expired || now()->greaterThan($accessToken->expires_at)) {
            $accessToken->is_expired = true;
            $accessToken->save();

            return response()->json(['message' => 'Token is expired.', 'error' => 'token_expired'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $accessToken->requests_count = $accessToken->requests_count + 1;
        $accessToken->save();

        JWTAuth::parseToken()->authenticate(); // authenticate user

        return $next($request);
    }
}
