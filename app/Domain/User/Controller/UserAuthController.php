<?php

namespace App\Domain\User\Controller;

use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use App\Domain\User\Models\UserActivationCode;
use App\Domain\User\Requests\UserAuthActivationRequest;
use App\Domain\User\Service\UserAuthService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

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
     * @OA\Post(
     *     path="/api/v1/auth/activation",
     *     summary="Activate member account",
     *     tags={"Member Authentication"},
     *     description="Activates a member account using the activation code.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email","activation_code"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="activation_code", type="string", example="A1B2C3")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=202,
     *         description="Account successfully activated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="status", type="string", example="Account activation has been activated."),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=406,
     *         description="Validation error"
     *     )
     * )
     */
    public function activation(Request $request): JsonResponse
    {
        $formRequest = new UserAuthActivationRequest;

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

        $user = User::where('email', $request->email)->with('activationCode')->first();

        $user->activationCode->is_active = true;
        $user->activationCode->save();

        return response()->json(
            [
                'email' => $user->email,
                'status' => 'Account activation has been activated.',
            ],
            JsonResponse::HTTP_ACCEPTED
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/refresh",
     *     summary="Refresh JWT token",
     *     tags={"Member Authentication"},
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
     *     path="/api/v1/auth/logout",
     *     summary="Logout member",
     *     tags={"Member Authentication"},
     *     description="Terminates the current JWT token and logs out the member.",
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
            return response()->json(['message' => 'Token invalid or expired.', 'error' => 'token_invalid'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $access->is_terminated = true;
        $access->save();

        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['token_expired' => $access->token], JsonResponse::HTTP_ACCEPTED);
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
