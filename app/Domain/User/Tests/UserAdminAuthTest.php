<?php

/** @var \Tests\TestCase $this */

use App\Domain\Admin\Models\Admin;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;

beforeEach(function () {
    Artisan::call('db:seed');

    $email = fake()->unique()->safeEmail();
    $this->payload = (object) [
        'email' => $email,
        'nickname' => preg_replace('/[^A-Za-z0-9]/', '', strstr($email, '@', true)),
        'password' => '12345678aZ!',
    ];
});

describe('User role admin auth token refresh fail - @POST /api/v1/admin/auth/refresh', function () {
    it('fails because authentication token is not found on access logs', function () {
        Admin::factory()->withAuth()->create();
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

describe('User role admin auth token refresh fail - @POST /api/v1/admin/auth/refresh', function () {
    it('fails because authentication token is terminated and cannot be refreshed', function () {
        $admin = Admin::factory()->withAuth()->create();
        $admin->load('user.adminAccessLogs');
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
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

describe('User role admin auth token refresh success - @POST /api/v1/admin/auth/refresh', function () {
    it('succeeds authentication token can be refreshed', function () {
        $admin = Admin::factory()->withAuth()->create();
        $admin->load('user.adminAccessLogs');
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
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

describe('User role admin logout success - @POST /api/v1/admin/auth/logout', function () {
    it('succeeds user authentication can logout', function () {
        $admin = Admin::factory()->withAuth()->create();
        $admin->load('user.adminAccessLogs');
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
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

describe('User role admin whoami fail - @GET /api/v1/admin/auth/whoami', function () {
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

describe('User role admin whoami fail - @GET /api/v1/admin/auth/whoami', function () {
    it('fails user cannot see its account main properties JWT is terminated by authentication logout', function () {
        $admin = Admin::factory()->withAuth()->create();
        $admin->load('user.adminAccessLogs');
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
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

describe('User role admin whoami success - @GET /api/v1/admin/auth/whoami', function () {
    it('succeeds user authenticated can see itself', function () {
        $admin = Admin::factory()->withAuth()->create();
        $admin->load(['profile', 'user.adminAccessLogs']);
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();

        $route = route('api-v1.auth.whoami');
        $response = $this->get($route, [
            'Authorization' => "Bearer $accessLog->token",
        ]);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('email', fn ($email) => $email === $admin->user->email)
                ->where('uid', fn ($uid) => $uid === $admin->uid)
                ->where('nickname', fn ($nickname) => $nickname === $admin->profile->nickname)
                ->etc()
            );
    });
});
