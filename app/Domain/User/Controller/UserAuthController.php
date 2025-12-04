<?php

namespace App\Domain\User\Controller;

use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use App\Domain\User\Models\UserActivationCode;
use App\Domain\User\Service\UserAuthService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tymon\JWTAuth\Exceptions\JWTException;
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

        $access = (new UserAuthService)->checkToken();
        if (! $access) {
            return response()->json(['message' => 'Token invalid or expired.', 'error' => 'token_invalid'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $userAccount = null;
        $userProfile = null;

        if ($user->role == Role::ADMIN) {
            $user->load(['admin', 'adminProfile']);
            $userAccount = $user->admin;
            $userProfile = $user->adminProfile;
        }

        if ($user->role == Role::MEMBER) {
            $user->load(['member', 'memberProfile']);
            $userAccount = $user->member;
            $userProfile = $user->memberProfile;
        }

        return response()->json(
            [
                'email' => $user->email,
                'uid' => $userAccount->uid,
                'nickname' => $userProfile->nickname,
                'avatar' => $userProfile->avatar,
                'token' => $access->token,
            ],
            JsonResponse::HTTP_OK
        );
    }
}
