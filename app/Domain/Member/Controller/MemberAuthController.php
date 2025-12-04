<?php

namespace App\Domain\Member\Controller;

use App\Domain\Member\Jobs\UserRegisterJob;
use App\Domain\Member\Mail\UserRegisterMail;
use App\Domain\Member\Models\Member;
use App\Domain\Member\Models\MemberAccessLog;
use App\Domain\Member\Models\MemberProfile;
use App\Domain\Member\Requests\MemberAuthRegisterRequest;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use App\Domain\User\Models\UserActivationCode;
use App\Domain\User\Requests\UserAuthActivationRequest;
use App\Domain\User\Service\UserAuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(
 *     name="Member Authentication",
 *     description="Endpoints about the authenticated user"
 * )
 */
class MemberAuthController extends Controller
{
    /**
     * JWT access expiration, smaller than JWT TTL config
     */
    protected $jwtTime = 60;

    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     summary="Register a new member",
     *     tags={"Member Authentication"},
     *     description="Registers a new member account and returns basic profile info and the activation code.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email","password","nickname"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="yourPassword123"),
     *             @OA\Property(property="nickname", type="string", example="JohnDoe"),
     *             @OA\Property(property="region_id", type="integer", example=1, nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="uid", type="integer", example=156490),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="nickname", type="string", example="johndoe"),
     *             @OA\Property(property="activation_code", type="string", example="A1B2C3"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=406,
     *         description="Validation error"
     *     )
     * )
     */
    public function register(Request $request): JsonResponse
    {
        $formRequest = new MemberAuthRegisterRequest;

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
            'role' => Role::MEMBER,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $member = Member::create([
            'user_id' => $user->id,
            'region_id' => $request->region_id ?? null,
        ]);

        $requiresActivation = (bool) env('LOGIN_ACTIVATION_CODE');
        $activation = UserActivationCode::create([
            'user_id' => $user->id,
            'is_active' => ! $requiresActivation,
        ]);

        $profile = MemberProfile::create([
            'user_id' => $user->id,
            'nickname' => $request->nickname,
        ]);

        $payload = [
            'uid' => $member->uid,
            'email' => $user->email,
            'nickname' => $profile->nickname,
            'activation_code' => $activation->code,
        ];

        if (env('MAIL_SEND') === true) {
            if (env('QUEUE_SEND') === true) {
                UserRegisterJob::dispatch($payload);
            } else {
                Mail::to($payload['email'])->send(new UserRegisterMail($payload));
            }
        }

        return response()->json($payload, JsonResponse::HTTP_CREATED);
    }

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
     *     path="/api/v1/auth/login",
     *     summary="Member login",
     *     tags={"Member Authentication"},
     *     description="Authenticates a member and returns a JWT token.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email","password"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="yourPassword123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Ok",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGci..."),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=406,
     *         description="Invalid credentials"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized due to account validation required"
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
        $credentials['role'] = Role::MEMBER;

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['message' => 'Invalid credentials.'], JsonResponse::HTTP_NOT_ACCEPTABLE);
            }

        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not create token.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $user = Auth::user();

        $configActivation = (bool) env('LOGIN_ACTIVATION_CODE');
        $userActivationCode = UserActivationCode::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
        if ($configActivation && ! $userActivationCode) {
            return response()->json(['message' => 'Access requires activation.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        MemberAccessLog::create([
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
            JsonResponse::HTTP_OK
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
     *     tags={"Member Authentication"},
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
        $user->load(['member', 'memberProfile']);

        $access = (new UserAuthService)->checkToken();
        if (! $access) {
            return response()->json(['message' => 'Token invalid or expired.', 'error' => 'token_invalid'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return response()->json(
            [
                'email' => $user->email,
                'uid' => $user->member->uid,
                'nickname' => $user->memberProfile->nickname,
                'avatar' => $user->memberProfile->avatar,
                'token' => $access->token,
            ],
            JsonResponse::HTTP_OK
        );
    }
}
