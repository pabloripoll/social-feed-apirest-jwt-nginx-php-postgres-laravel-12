<?php

namespace App\Modules\User\Controller;

use App\Modules\User\Models\Role;
use App\Modules\User\Models\User;
use App\Modules\User\Requests\UserAuthActivationRequest;
use App\Modules\User\Service\UserAuthService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(
 *     name="User Authentication",
 *     description="Endpoints about the authenticated user"
 * )
 */
class UserAuthController extends Controller
{
    /**
     * JWT access expiration, smaller than JWT TTL config
     */
    protected $jwtTime = 60;

    /**
     * @OA\Get(
     *     path="/activate/{code}",
     *     summary="Activate user account",
     *     tags={"User Authentication"},
     *     description="Activates a user account using the activation code from URL.",
     *
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         required=true,
     *         description="The 32-character activation code",
     *
     *         @OA\Schema(type="string", example="a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Account has been already activated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="Account has been already activated."),
     *             @OA\Property(property="code", type="string", example="a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=202,
     *         description="Account successfully activated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="Account successfully activated."),
     *             @OA\Property(property="code", type="string", example="a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Invalid activation code",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Invalid or expired activation code."),
     *             @OA\Property(property="error", type="string", example="code")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=406,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="The code field is required."),
     *             @OA\Property(property="error", type="string", example="code")
     *         )
     *     )
     * )
     */
    public function activate(UserAuthActivationRequest $request): JsonResponse
    {
        // Get the entity from the request
        $activation = $request->getActivationCode();

        if (! $activation) {
            return response()->json(
                [
                    'message' => 'Invalid or expired activation code.',
                    'error' => 'not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        // Check if already activated
        if ($activation->is_active) {
            return response()->json(
                [
                    'message' => 'Account has been already activated.',
                    'code' => $activation->code,
                ],
                JsonResponse::HTTP_OK
            );
        }

        $activation->user->email_verified_at = Carbon::now();
        $activation->user->save();

        $activation->is_active = true;
        $activation->save();

        return response()->json(
            [
                'message' => 'Account successfully activated.',
                'email' => $activation->user->email,
            ],
            JsonResponse::HTTP_ACCEPTED
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/refresh",
     *     summary="Refresh JWT token",
     *     tags={"User Authentication"},
     *     description="Refreshes the JWT token for the authenticated user.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=202,
     *         description="Token refreshed",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGci..."),
     *             @OA\Property(property="token_expired", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGci..."),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *
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
            return response()->json(
                [
                    'message' => 'Token invalid.',
                    'error' => 'token_invalid',
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $legacyToken = $access->token;

        /** @var Illuminate\Auth\AuthManager */
        $auth = auth('api');

        $refreshedToken = JWTAuth::refresh($legacyToken);

        $access->is_expired = false;
        $access->expires_at = now()->addMinutes($this->jwtTime);
        $access->refresh_count = $access->refresh_count + 1;
        $access->token = (string) $refreshedToken;
        $access->save();

        return response()->json(
            [
                'token_refreshed' => $access->token,
                'expires_in' => $auth->factory()->getTTL() * $this->jwtTime,
                'token_expired' => $legacyToken,
            ],
            JsonResponse::HTTP_ACCEPTED
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     summary="Logout user",
     *     tags={"User Authentication"},
     *     description="Terminates the current JWT token and logs out the user.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=202,
     *         description="Token terminated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token_expired", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGci...")
     *         )
     *     ),
     *
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
            return response()->json(
                [
                    'message' => 'Token invalid.',
                    'error' => 'token_invalid',
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $access->is_terminated = true;
        $access->save();

        JWTAuth::invalidate($access->token);

        return response()->json(
            [
                'message' => 'User session successfully terminated.',
                'token_expired' => $access->token,
            ],
            JsonResponse::HTTP_ACCEPTED
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/whoami",
     *     summary="Get authenticated user info",
     *     tags={"User Authentication"},
     *     description="Returns information about the authenticated user.",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="email", type="string", example="john@example.com"),
     *              @OA\Property(property="uid", type="integer", example=156490),
     *              @OA\Property(property="nickname", type="string", example="JohnDoe"),
     *              @OA\Property(property="avatar", type="string", example="http://..."),
     *         )
     *     ),
     *
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

        $userAccount = null;
        $userProfile = null;
        $userLatestAccess = null;

        if ($user->role == Role::ADMIN) {
            $user->load(['admin', 'adminProfile', 'latestAdminAccessLog']);
            $userAccount = $user->admin;
            $userProfile = $user->adminProfile;
            $userLatestAccess = $user->latestAdminAccessLog;
        }

        if ($user->role == Role::MEMBER) {
            $user->load(['member', 'memberProfile', 'latestMemberAccessLog']);
            $userAccount = $user->member;
            $userProfile = $user->memberProfile;
            $userLatestAccess = $user->latestMemberAccessLog;
        }

        return response()->json(
            [
                'email' => $user->email,
                'uid' => $userAccount->uid,
                'nickname' => $userProfile->nickname,
                'avatar' => $userProfile->avatar,
                'token' => $userLatestAccess->token,
            ],
            JsonResponse::HTTP_OK
        );
    }
}
