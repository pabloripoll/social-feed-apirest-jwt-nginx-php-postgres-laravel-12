<?php

/** @var \Tests\TestCase $this */

use App\Domain\Member\Models\Member;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;

beforeEach(function () {
    $email = fake()->unique()->safeEmail();
    $this->payload = (object) [
        'email' => $email,
        'nickname' => preg_replace('/[^A-Za-z0-9]/', '', strstr($email, '@', true)),
        'password' => '12345678aZ!',
    ];
});

describe('User role member activation - @POST /api/v1/auth/activation', function () {
    it('succeeds that a user can activate its account access', function () {
        $route = route('api-v1.member-account.register');
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
        $activationCode = $data['activation_code'];

        $route = route('api-v1.auth.activation');
        $payload = [
            'code' => $activationCode,
            'email' => $email,
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_ACCEPTED);
    });
});

describe('User role member auth token refresh fail - @POST /api/v1/auth/refresh', function () {
    it('fails because authentication token is not found on access logs', function () {
        Member::factory()->withAuth()->create();
        $wrongJwt = Str::random(64);
        $route = route('api-v1.auth.refresh');
        $response = $this->post($route, [], [
            'Authorization' => "Bearer $wrongJwt",
        ]);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('message', fn ($message) => is_string($message))
                ->where('error', fn ($error) => $error === 'token_invalid')
                ->etc()
            );
    });
});

describe('User role member auth token refresh fail - @POST /api/v1/auth/refresh', function () {
    it('fails because authentication token is terminated and cannot be refreshed', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load('user.memberAccessLogs');
        $accessLog = $member->user->memberAccessLogs()->latest()->first();
        $accessLog->is_terminated = true;
        $accessLog->save();
        $route = route('api-v1.auth.refresh');
        $response = $this->post($route, [], [
            'Authorization' => "Bearer $accessLog->token",
        ]);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('message', fn ($message) => is_string($message))
                ->where('error', fn ($error) => $error === 'token_invalid')
                ->etc()
            );
    });
});

describe('User role member auth token refresh success - @POST /api/v1/auth/refresh', function () {
    it('succeeds authentication token can be refreshed', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load('user.memberAccessLogs');
        $accessLog = $member->user->memberAccessLogs()->latest()->first();
        $accessLog->is_expired = true;
        $accessLog->expires_at = now();
        $accessLog->save();
        $route = route('api-v1.auth.refresh');
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

describe('User role member logout success - @POST /api/v1/auth/logout', function () {
    it('succeeds user authentication can logout', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load('user.memberAccessLogs');
        $accessLog = $member->user->memberAccessLogs()->latest()->first();
        $accessLog->is_expired = true;
        $accessLog->expires_at = now();
        $accessLog->save();
        $route = route('api-v1.auth.logout');
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

describe('User role member whoami fail - @GET /api/v1/auth/whoami', function () {
    it('fails user cannot see its account main properties if there is no JWT', function () {
        $route = route('api-v1.auth.whoami');
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'token_not_provided')
                ->etc()
            );
    });
});

describe('User role member whoami fail - @GET /api/v1/auth/whoami', function () {
    it('fails user cannot see its account main properties JWT is terminated by authentication logout', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load('user.memberAccessLogs');
        $accessLog = $member->user->memberAccessLogs()->latest()->first();
        $accessLog->is_terminated = true;
        $accessLog->save();
        $route = route('api-v1.auth.whoami');
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

describe('User role member whoami success - @GET /api/v1/auth/whoami', function () {
    it('succeeds user authenticated can see itself', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['profile', 'user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();
        $route = route('api-v1.auth.whoami');
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
