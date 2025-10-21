<?php

namespace App\Domain\Admin\Controller;

use App\Domain\Admin\Models\Admin;
use App\Domain\Admin\Models\AdminAccessLog;
use App\Domain\Admin\Models\AdminProfile;
use App\Domain\Admin\Requests\AdminAuthRegisterRequest;
use App\Http\Controllers\Controller;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use App\Domain\User\Service\UserAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(
 *     name="Admin Authentication",
 *     description="Endpoints about the authenticated user"
 * )
 */
class AdminAuthController extends Controller
{
    /**
     * JWT access expiration, smaller than JWT TTL config
     */
    protected $jwtTime = 60;

    /**
     * @OA\Post(
     *     path="/api/v1/admin/auth/register",
     *     summary="Register a new member",
     *     tags={"Admin Authentication"},
     *     description="Registers a new member account and returns basic profile info and the activation code.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password","nickname"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="yourPassword123"),
     *             @OA\Property(property="nickname", type="string", example="JohnDoe"),
     *             @OA\Property(property="region_id", type="integer", example=1, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="nickname", type="string", example="johndoe"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=406,
     *         description="Validation error"
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/v1/admin/auth/login",
     *     summary="Admin login",
     *     tags={"Admin Authentication"},
     *     description="Authenticates a member and returns a JWT token.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="yourPassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Accepted",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGci..."),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(
     *         response=406,
     *         description="Invalid credentials"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Could not create token"
     *     )
     * )
     */
    public function login(Request $request): JsonResponse
    {
        /** @var Illuminate\Auth\AuthManager */
        $auth = auth('api');
        $credentials = $request->only('email', 'password');
        $credentials['role'] = Role::ADMIN;

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['message' => 'Invalid credentials.'], JsonResponse::HTTP_NOT_ACCEPTABLE);
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
     * @OA\Post(
     *     path="/api/v1/admin/auth/refresh",
     *     summary="Refresh JWT token",
     *     tags={"Admin Authentication"},
     *     description="Refreshes the JWT token for the authenticated user.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=202,
     *         description="Token refreshed",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGci..."),
     *             @OA\Property(property="token_expired", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGci..."),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized refresh due to invalid token"
     *     )
     * )
     */
    public function refresh(): JsonResponse
    {
        $access = (new UserAuthService)->checkToken();
        if (! $access) {
            return response()->json(['message' => 'Token invalid or expired.', 'error' => 'token_invalid'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $legacyToken = $access->token;

        /** @var Illuminate\Auth\AuthManager */
        $auth = auth('api');

        $refreshedToken = JWTAuth::refresh(JWTAuth::getToken());

        $access->expires_at = now()->addMinutes($this->jwtTime);
        $access->refresh_count = $access->refresh_count + 1;
        $access->token = (string) $refreshedToken;
        $access->save();

        return response()->json(
            [
                'token' => $access->token,
                'token_expired' => $legacyToken,
                'expires_in' => $auth->factory()->getTTL() * $this->jwtTime,
            ],
            JsonResponse::HTTP_ACCEPTED
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/auth/logout",
     *     summary="Logout member",
     *     tags={"Admin Authentication"},
     *     description="Terminates the current JWT token and logs out the member.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=202,
     *         description="Token terminated",
     *         @OA\JsonContent(
     *             @OA\Property(property="token_expired", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGci...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access due to invalid token"
     *     )
     * )
     */
    public function logout(): JsonResponse
    {
        $access = (new UserAuthService)->checkToken();
        if (! $access) {
            return response()->json(['message' => 'Token invalid or expired.', 'error' => 'token_invalid'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $access->is_terminated = true;
        $access->save();

        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['token_expired' => $access->token], JsonResponse::HTTP_ACCEPTED);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/auth/whoami",
     *     summary="Get authenticated user info",
     *     tags={"Admin Authentication"},
     *     description="Returns information about the authenticated user.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="email", type="string", example="john@example.com"),
     *              @OA\Property(property="nickname", type="string", example="JohnDoe"),
     *              @OA\Property(property="avatar", type="string", example="http://..."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access due to invalid token"
     *     )
     * )
     */
    public function whoami(): JsonResponse
    {
        /** @var Illuminate\Auth\AuthManager $user */
        $user = Auth::user();
        $user->load(['admin', 'adminProfile']);

        $access = (new UserAuthService)->checkToken();
        if (! $access) {
            return response()->json(['message' => 'Token invalid or expired.', 'error' => 'token_invalid'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return response()->json(
            [
                'email' => $user->email,
                'nickname' => $user->adminProfile->nickname,
                'avatar' => $user->adminProfile->avatar,
                'token' => $access->token,
            ],
            JsonResponse::HTTP_OK
        );
    }
}
