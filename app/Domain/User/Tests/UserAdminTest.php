<?php

/** @var \Tests\TestCase $this */

use App\Domain\Admin\Models\Admin;
use App\Domain\Member\Models\Member;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Artisan::call('db:seed');
});

describe('User Admins - Listing - @GET /api/v1/users/admins', function () {
    it('fails a not authenticated user nor any member can access to any of admin users management routes', function () {
        $route = route('api-v1.users.admins.listing');
        $response = $this->getJson($route);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'token_not_provided')
                ->etc()
            );
        $member = Member::factory()->withAuth()->create();
        $member->load(['user', 'user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();
        $route = route('api-v1.users.admins.listing');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'forbidden_access')
                ->etc()
            );
    });
});

describe('User Admins - Listing - @GET /api/v1/users/admins', function () {
    it('succeeds admin users can list admin users', function () {
        $admin = Admin::factory()->withAuth()->create();
        $admin->load(['user', 'user.adminAccessLogs']);
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
        $route = route('api-v1.users.admins.listing');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('filters', fn (AssertableJson $json) => $json
                    ->has('sorting', fn (AssertableJson $json) => $json
                        ->where('recent', 'Recent')
                        ->where('oldest', 'Oldest')
                    )
                    ->etc()
                )
                ->has('listing', fn (AssertableJson $listing) => $listing
                    ->whereType('page', 'integer')
                    ->whereType('limit', 'integer')
                    ->whereType('total', 'integer')
                    ->whereType('pages', 'integer')
                    ->whereType('first_page', 'string')
                    ->whereType('last_page', 'string')
                    ->etc()
                )
                ->has('result')
                ->whereType('result', 'array')
                ->etc()
            );
        expect($response->json('listing')['total'])->toBeGreaterThanOrEqual(1);
    });
});

describe('User Admins - Read - @GET /api/v1/users/admins/{id}', function () {
    it('succeeds admin users can read any admin base data', function () {
        $admin = Admin::factory()->withAuth()->create();
        $admin->load(['user', 'user.adminAccessLogs']);
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
        $sampleAdmin = Admin::first();
        $route = route('api-v1.users.admins.read', ['id' => $sampleAdmin->user_id]);
        $response = $this->withToken($accessLog->token)->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('id', fn ($id) => $id === $sampleAdmin->user_id)
                ->where('uid', fn ($uid) => $uid === $sampleAdmin->uid)
                ->whereType('email', 'string')
                ->whereType('nickname', 'string')
                ->whereType('created_at', 'string')
                ->whereType('last_access', 'array')
                ->etc()
            );
    });
});

describe('User Admins - Profile - @GET /api/v1/users/admins/{id}/profile', function () {
    it('succeeds admin users can read any admin profile', function () {
        $admin = Admin::factory()->withAuth()->create();
        $admin->load(['user', 'user.adminAccessLogs']);
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
        $sampleAdmin = Admin::first();
        $route = route('api-v1.users.admins.profile-read', ['id' => $sampleAdmin->user_id]);
        $response = $this->withToken($accessLog->token)->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('id', fn ($id) => $id === $sampleAdmin->user_id)
                ->where('uid', fn ($uid) => $uid === $sampleAdmin->uid)
                ->whereType('email', 'string')
                ->whereType('nickname', 'string')
                ->where('avatar', fn ($avatar) => is_string($avatar) || is_null($avatar))
                ->where('continent_id', fn ($continent_id) => is_integer($continent_id) || is_null($continent_id))
                ->where('continent_name', fn ($continent_name) => is_string($continent_name) || is_null($continent_name))
                ->where('region_id', fn ($region_id) => is_integer($region_id) || is_null($region_id))
                ->where('region_name', fn ($region_name) => is_string($region_name) || is_null($region_name))
                ->whereType('is_active', 'boolean')
                ->whereType('is_banned', 'boolean')
                ->whereType('created_at', 'string')
                ->whereType('updated_at', 'string')
                ->etc()
            );
    });
});
