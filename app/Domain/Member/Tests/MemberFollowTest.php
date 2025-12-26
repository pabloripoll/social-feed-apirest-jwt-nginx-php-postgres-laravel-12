<?php

/** @var \Tests\TestCase $this */

use App\Domain\Member\Models\Member;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Artisan::call('db:seed');
});

describe('Member Follow - @POST /api/v1/members/{member_uid}/follow', function () {
    it('fails a member user can follow another member account if auth token is invalid', function () {
        $member_b = Member::factory()->withAuth()->create();
        $member_b->load(['profile']);
        $route = route('api-v1.members.profile-follow', ['member_uid' => $member_b->uid]);
        $response = $this->withToken(fakeJWT())->post($route, []);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'token_invalid')
                ->etc()
            );
    });
});

describe('Member Follow - @POST /api/v1/members/{member_uid}/follow', function () {
    it('fails a member user can follow another member account if uid is invalid', function () {
        $member_a = Member::factory()->withAuth()->create();
        $member_a->load(['user', 'profile', 'user.memberAccessLogs']);
        $accessLog = $member_a->user->memberAccessLogs()->latest()->first();
        $route = route('api-v1.members.profile-follow', ['member_uid' => 123456]);
        $response = $this->withToken($accessLog->token)->post($route, []);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'member_not_found')
                ->etc()
            );
    });
});

describe('Member Follow - @POST /api/v1/members/{member_uid}/follow', function () {
    it('succeeds a member user can follow another member account', function () {
        $member_a = Member::factory()->withAuth()->create();
        $member_a->load(['user', 'profile', 'user.memberAccessLogs']);
        $accessLog = $member_a->user->memberAccessLogs()->latest()->first();

        $member_b = Member::factory()->withAuth()->create();
        $member_b->load(['profile']);

        $route = route('api-v1.members.profile-follow', ['member_uid' => $member_b->uid]);
        $response = $this->withToken($accessLog->token)->post($route, []);
        $response->assertStatus(JsonResponse::HTTP_CREATED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereType('message', 'string')
                ->has('follower', fn (AssertableJson $follower) => $follower
                    ->where('uid', fn ($uid) => $uid == $member_a->uid)
                    ->where('nickname', fn ($nickname) => $nickname == $member_a->profile->nickname)
                    ->has('avatar')
                    ->etc()
                )
                ->has('following', fn (AssertableJson $following) => $following
                    ->where('uid', fn ($uid) => $uid == $member_b->uid)
                    ->where('nickname', fn ($nickname) => $nickname == $member_b->profile->nickname)
                    ->has('avatar')
                    ->etc()
                )
                ->etc()
            );
    });
});

describe('Member Unfollow - @POST /api/v1/members/{member_uid}/unfollow', function () {
    it('succeeds a member user can unfollow already following account', function () {
        $member_a = Member::factory()->withAuth()->create();
        $member_a->load(['user', 'profile', 'user.memberAccessLogs']);
        $accessLog = $member_a->user->memberAccessLogs()->latest()->first();

        $member_b = Member::factory()->withAuth()->create();
        $member_b->load(['profile']);

        $route = route('api-v1.members.profile-follow', ['member_uid' => $member_b->uid]);
        $response = $this->withToken($accessLog->token)->post($route, []);

        $route = route('api-v1.members.profile-unfollow', ['member_uid' => $member_b->uid]);
        $response = $this->withToken($accessLog->token)->post($route, []);
        $response->assertStatus(JsonResponse::HTTP_ACCEPTED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereType('message', 'string')
                ->has('follower', fn (AssertableJson $follower) => $follower
                    ->where('uid', fn ($uid) => $uid == $member_a->uid)
                    ->where('nickname', fn ($nickname) => $nickname == $member_a->profile->nickname)
                    ->has('avatar')
                    ->etc()
                )
                ->has('following', fn (AssertableJson $following) => $following
                    ->where('uid', fn ($uid) => $uid == $member_b->uid)
                    ->where('nickname', fn ($nickname) => $nickname == $member_b->profile->nickname)
                    ->has('avatar')
                    ->etc()
                )
                ->etc()
            );
    });
});


describe('Member Unfollow - @POST /api/v1/members/{member_uid}/unfollow', function () {
    it('fails a member user is able to unfollow another member account if uid is invalid', function () {
        $member_a = Member::factory()->withAuth()->create();
        $member_a->load(['user', 'profile', 'user.memberAccessLogs']);
        $accessLog = $member_a->user->memberAccessLogs()->latest()->first();
        $route = route('api-v1.members.profile-follow', ['member_uid' => 123456]);
        $response = $this->withToken($accessLog->token)->post($route, []);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'member_not_found')
                ->etc()
            );
    });
});
