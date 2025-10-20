<?php

use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\Member\Models\Member;

/** @var \Tests\TestCase $this */
beforeEach(function () {

    $email = fake()->unique()->safeEmail();
    $this->payload = (object) [
        'email' => $email,
        'nickname' => preg_replace('/[^A-Za-z0-9]/', '', strstr($email, '@', true)),
        'password' => '12345678aZ!',
    ];
});

describe('Member user registration fail - @POST /api/v1/auth/register', function () {
    it('fails that a user can register because of wrong nickname', function () {
        $route = route('api-v1.member-auth.register');
        $payload = [
            'email' => $this->payload->email,
            'password' => $this->payload->password,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'nickname')
                ->etc()
            );
        $payload = [
            'nickname' => $this->payload->nickname.'@',
            'email' => $this->payload->email,
            'password' => $this->payload->password,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'nickname')
                ->etc()
            );
    });
});

describe('Member user registration fail - @POST /api/v1/auth/register', function () {
    it('fails that a user can register because of wrong email', function () {
        $route = route('api-v1.member-auth.register');
        $payload = [
            'nickname' => $this->payload->nickname,
            'password' => $this->payload->password,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'email')
                ->etc()
            );
        $payload = [
            'nickname' => $this->payload->nickname,
            'email' => '@'.$this->payload->email,
            'password' => $this->payload->password,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'email')
                ->etc()
            );
    });
});

describe('Member user registration fail - @POST /api/v1/auth/register', function () {
    it('fails that a user can register because of wrong password', function () {
        $route = route('api-v1.member-auth.register');
        // missing password field
        $payload = [
            'nickname' => $this->payload->nickname,
            'email' => $this->payload->email,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'password')
                ->etc()
            );
        // missing password_confirmation field
        $payload = [
            'nickname' => $this->payload->nickname,
            'email' => $this->payload->email,
            'password' => $this->payload->password,
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'password')
                ->etc()
            );
        // password confirmation mismatch
        $payload = [
            'nickname' => $this->payload->nickname,
            'email' => $this->payload->email,
            'password' => $this->payload->password,
            'password_confirmation' => $this->payload->password.'?',
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'password')
                ->etc()
            );
        // password confirmation no comply
        $payload = [
            'nickname' => $this->payload->nickname,
            'email' => $this->payload->email,
            'password' => '1234aZ!',
            'password' => '1234aZ!',
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'password')
                ->etc()
            );
    });
});

describe('Member user registration success - @POST /api/v1/auth/register', function () {
    it('succeeds that a user can register by itself as a member', function () {
        $route = route('api-v1.member-auth.register');
        $payload = [
            'nickname' => $this->payload->nickname,
            'email' => $this->payload->email,
            'password' => $this->payload->password,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_CREATED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('email')
                ->has('nickname')
                ->where('uid', fn ($uid) => is_int($uid))
                ->etc()
            );
    });
});

describe('Member user activation - @POST /api/v1/auth/activation', function () {
    it('succeeds that a user can activate its account access', function () {
        $route = route('api-v1.member-auth.register');
        $payload = [
            'nickname' => $this->payload->nickname,
            'email' => $this->payload->email,
            'password' => $this->payload->password,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_CREATED);

        $data = $response->json();
        $email = $data['email'];
        $activationCode  = $data['activation_code'];

        $route = route('api-v1.member-auth.activation');
        $payload = [
            'code' => $activationCode,
            'email' => $email,
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_ACCEPTED);
    });
});

describe('Member user login fail - @POST /api/v1/auth/login', function () {
    it('fails when user login input is a wrong password', function () {
        $member = Member::factory()->create();
        $route = route('api-v1.member-auth.login');
        $payload = [
            'email' => $member->user->email,
            'password' => 'wrong-password',
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('message', fn ($message) => is_string($message))
                ->etc()
            );
    });
});

describe('Member user login success- @POST /api/v1/auth/login', function () {
    it('succeeds that a user can log into its account', function () {
        $member = Member::factory()->create();
        $route = route('api-v1.member-auth.login');
        $payload = [
            'email' => $member->user->email,
            'password' => 'password',
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_ACCEPTED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('token', fn ($token) => is_string($token))
                ->where('expires_in', fn ($expires_in) => is_int($expires_in))
                ->etc()
            );
    });
});

describe('Member auth token refresh fail - @POST /api/v1/auth/refresh', function () {
    it('fails because authentication token is not found on access logs', function () {
        Member::factory()->withAuth()->create();
        $wrongJwt = Str::random(64);
        $route = route('api-v1.member-auth.refresh');
        $response = $this->post($route, [], [
            'Authorization' => "Bearer $wrongJwt",
        ]);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('message', fn ($message) => is_string($message))
                ->where('error', fn ($error) => $error === 'token_not_found')
                ->etc()
            );
    });
});

describe('Member auth token refresh fail - @POST /api/v1/auth/refresh', function () {
    it('fails because authentication token is terminated and cannot be refreshed', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load('user.accessLogs');
        $accessLog = $member->user->accessLogs()->latest()->first();
        $accessLog->is_terminated = true;
        $accessLog->save();
        $route = route('api-v1.member-auth.refresh');
        $response = $this->post($route, [], [
            'Authorization' => "Bearer $accessLog->token",
        ]);
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('message', fn ($message) => is_string($message))
                ->where('error', fn ($error) => $error === 'token_terminated')
                ->etc()
            );
    });
});

describe('Member auth token refresh success - @POST /api/v1/auth/refresh', function () {
    it('succeeds authentication token can be refreshed', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load('user.accessLogs');
        $accessLog = $member->user->accessLogs()->latest()->first();
        $accessLog->is_expired = true;
        $accessLog->expires_at = now();
        $accessLog->save();
        $route = route('api-v1.member-auth.refresh');
        $response = $this->post($route, [], [
            'Authorization' => "Bearer $accessLog->token",
        ]);
        $response->assertStatus(JsonResponse::HTTP_ACCEPTED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('token')
                ->has('token_expired')
                ->has('expires_in')
                ->etc()
            );
    });
});

describe('Member logout success - @POST /api/v1/auth/logout', function () {
    it('succeeds user authentication can logout', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load('user.accessLogs');
        $accessLog = $member->user->accessLogs()->latest()->first();
        $accessLog->is_expired = true;
        $accessLog->expires_at = now();
        $accessLog->save();
        $route = route('api-v1.member-auth.logout');
        $response = $this->post($route, [], [
            'Authorization' => "Bearer $accessLog->token",
        ]);
        $response->assertStatus(JsonResponse::HTTP_ACCEPTED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('token_expired')
                ->etc()
            );
    });
});

describe('Member whoami fail - @GET /api/v1/auth/whoami', function () {
    it('fails user cannot see its account main properties if there is no JWT', function () {
        $route = route('api-v1.member-auth.whoami');
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'token_not_provided')
                ->etc()
            );
    });
});

describe('Member whoami fail - @GET /api/v1/auth/whoami', function () {
    it('fails user cannot see its account main properties JWT is terminated by authentication logout', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load('user.accessLogs');
        $accessLog = $member->user->accessLogs()->latest()->first();
        $accessLog->is_terminated = true;
        $accessLog->save();
        $route = route('api-v1.member-auth.whoami');
        $response = $this->get($route, [
            'Authorization' => "Bearer $accessLog->token",
        ]);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('message', fn ($message) => is_string($message))
                ->where('error', fn ($error) => $error === 'token_terminated')
                ->etc()
            );
    });
});

describe('Member whoami success - @GET /api/v1/auth/whoami', function () {
    it('succeeds user authenticated can see itself', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['profile', 'user.accessLogs']);
        $accessLog = $member->user->accessLogs()->latest()->first();
        $route = route('api-v1.member-auth.whoami');
        $response = $this->get($route, [
            'Authorization' => "Bearer $accessLog->token",
        ]);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('email', fn ($email) => $email === $member->user->email)
                ->where('uid', fn ($uid) => $uid === $member->uid)
                ->where('nickname', fn ($nickname) => $nickname === $member->profile->nickname)
                ->etc()
            );
    });
});
