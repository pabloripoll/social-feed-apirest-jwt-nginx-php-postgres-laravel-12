<?php

/** @var \Tests\TestCase $this */

use App\Domain\Member\Models\Member;
use App\Support\Debug;
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

describe('Non authenticated user on creating a feed post - @POST /api/v1/account/feed/posts', function () {
    it('succeeds a not authenticated user cannot access to protected route without header token', function () {
        $route = route('api-v1.account-feed.post-create');
        $response = $this->post($route, []);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('message', fn ($message) => is_string($message))
                ->where('error', fn ($error) => $error === 'token_not_provided')
                ->etc()
            );
    });
});

describe('Wrong authenticated user on creating a feed post - @POST /api/v1/account/feed/posts', function () {
    it('succeeds a user cannot access to protected route with an invalid token', function () {
        $route = route('api-v1.account-feed.post-create');
        $fakeToken = fakeJWT();
        $response = $this->post($route, [], [
            'Authorization' => "Bearer " . $fakeToken,
        ]);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('message', fn ($message) => is_string($message))
                ->where('error', fn ($error) => $error === 'token_invalid')
                ->etc()
            );
    });
});

describe('Member user on creating a feed post - @POST /api/v1/account/feed/posts', function () {
    it('succeeds authenticated member user can create a feed post', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();
        $route = route('api-v1.account-feed.post-create');
        $response = $this->post($route, [], [
            'Authorization' => "Bearer $accessLog->token",
        ]);
        $response->assertStatus(JsonResponse::HTTP_CREATED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('post_uid')
                ->etc()
            );
    });
});