<?php

namespace App\Domain\Admin\Controller;

use App\Domain\Admin\Models\Admin;
use App\Domain\Admin\Models\AdminAccessLog;
use App\Domain\Admin\Models\AdminProfile;
use App\Domain\Admin\Requests\AdminAuthRegisterRequest;
use App\Http\Controllers\Controller;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminAuthController extends Controller
{
    /**
     * JWT access expiration, smaller than JWT TTL config
     */
    protected $jwtTime = 60;

    /**
     * Object protected method to check JWT
     */
    protected function checkToken()
    {
        $token = JWTAuth::getToken();
        if (! $token) {
            return response()->json(['message' => 'Token not provided.', 'error' => 'token_not_provided'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        try {
            JWTAuth::decode($token);

            return (string) $token;

        } catch (JWTException $e) {
            return response()->json(['message' => 'Token invalid or expired.', 'error' => 'token_invalid'], JsonResponse::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * POST /api/v1/admin/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $formRequest = new AdminAuthRegisterRequest;

        $validator = Validator::make(
            $request->all(),
            $formRequest->rules(),
            $formRequest->messages()
        );
        if ($validator->fails()) {
            $errors = (array) $validator->errors()->messages();
            $field = array_key_first($errors);

            return response()->json(['message' => $errors[$field][0], 'error' => $field], JsonResponse::HTTP_NOT_ACCEPTABLE);
        }

        $user = User::create([
            'role' => Role::ADMIN,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Admin::create([
            'user_id' => $user->id,
            'region_id' => $request->region_id ?? null,
        ]);

        $profile = AdminProfile::create([
            'user_id' => $user->id,
            'nickname' => $request->nickname,
        ]);

        return response()->json(
            [
                'email' => $user->email,
                'nickname' => $profile->nickname,
            ],
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * POST /api/v1/admin/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        /** @var Illuminate\Auth\AuthManager */
        $auth = auth('api');
        $credentials = $request->only('email', 'password');
        $credentials['role'] = Role::ADMIN;

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['message' => 'Invalid credentials.'], JsonResponse::HTTP_UNAUTHORIZED);
            }

        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not create token.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $user = Auth::user();

        AdminAccessLog::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addMinutes($this->jwtTime),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_activity' => now(),
            'requests_count' => 1,
            'payload' => json_encode([]),
        ]);

        return response()->json(
            [
                'token' => $token,
                'expires_in' => $auth->factory()->getTTL() * $this->jwtTime,
            ],
            JsonResponse::HTTP_ACCEPTED
        );
    }

    /**
     * POST /api/v1/admin/auth/refresh
     */
    public function refresh(): JsonResponse
    {
        $jwtString = $this->checkToken();

        /** @var Illuminate\Auth\AuthManager */
        $auth = auth('api');

        $accessToken = AdminAccessLog::where('token', $jwtString)->first();
        if (! $accessToken) {
            return response()->json(['message' => 'Token not registered.', 'error' => 'token_not_found'], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($accessToken->is_terminated) {
            return response()->json(['message' => 'Token cannot be refreshed.', 'error' => 'token_terminated'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $refreshedToken = JWTAuth::refresh(JWTAuth::getToken());

        $accessToken->expires_at = now()->addMinutes($this->jwtTime);
        $accessToken->refresh_count = $accessToken->refresh_count + 1;
        $accessToken->token = (string) $refreshedToken;
        $accessToken->save();

        return response()->json(
            [
                'token' => $accessToken->token,
                'token_expired' => $jwtString,
                'expires_in' => $auth->factory()->getTTL() * $this->jwtTime,
            ],
            JsonResponse::HTTP_ACCEPTED
        );
    }

    /**
     * POST /api/v1/admin/auth/logout
     */
    public function logout(): JsonResponse
    {
        $jwtString = $this->checkToken();

        $accessToken = AdminAccessLog::where('token', $jwtString)->first();
        if (! $accessToken) {
            return response()->json(['message' => 'Token not registered.', 'error' => 'token_not_found'], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($accessToken->is_terminated) {
            return response()->json(['message' => 'Token is already terminated.', 'error' => 'token_terminated'], JsonResponse::HTTP_NOT_MODIFIED);
        }

        $accessToken->is_terminated = true;
        $accessToken->save();

        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['token_expired' => $jwtString], JsonResponse::HTTP_ACCEPTED);
    }

    /**
     * GET /api/v1/admin/auth/whoami
     */
    public function whoami(): JsonResponse
    {
        /** @var Illuminate\Auth\AuthManager $user */
        $user = Auth::user();
        $user->load(['admin', 'adminProfile']);

        return response()->json(
            [
                'email' => $user->email,
                'admin' => $user->admin->uid,
                'nickname' => $user->adminProfile->nickname,
                'avatar' => $user->adminProfile->avatar,
            ],
            JsonResponse::HTTP_OK
        );
    }
}
