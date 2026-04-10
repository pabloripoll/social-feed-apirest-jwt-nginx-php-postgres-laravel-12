<?php

/** @var \Tests\TestCase $this */

use App\Modules\Admin\Models\Admin;
use App\Modules\Member\Models\Member;
use App\Modules\User\Models\UserModerationSanction;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;

beforeEach(function () {
    Artisan::call('db:seed');
});

describe('User Moderation Sanctions - Listing - @GET /api/v1/moderations/sanctions', function () {
    it('fails a not authenticated user nor any member can access to any of user moderation sanctions management routes', function () {
        /** @var \Tests\TestCase $this */
        $route = route('api-v1.moderations.sanctions-listing');
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
        $route = route('api-v1.moderations.sanctions-listing');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'forbidden_access')
                ->etc()
            );
    });
});

describe('User Moderation Sanctions - Listing - @GET /api/v1/moderations/sanctions', function () {
    it('succeeds user admin can access and listing sanctions', function () {
        /** @var \Tests\TestCase $this */
        $admin = Admin::factory()->withAuth()->create();
        $admin->load(['user', 'user.adminAccessLogs']);
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
        $route = route('api-v1.moderations.sanctions-listing');
        $response = $this->withToken($accessLog->token)->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->each(fn (AssertableJson $item) => $item
                    ->whereType('id', 'integer')
                    ->whereType('key', 'string')
                    ->whereType('position', 'integer')
                    ->whereType('title', 'string')
                    ->whereType('description', 'string')
                    ->whereType('created_at', 'string')
                    ->whereType('updated_at', 'string')
                    ->etc()
                )
                ->etc()
            );
    });
});

describe('User Moderation Sanctions - Create - @POST /api/v1/moderations/sanctions', function () {
    it('succeeds user admin can create a new sanction', function () {
        /** @var \Tests\TestCase $this */
        $admin = Admin::factory()->withAuth()->create();
        $admin->load(['user', 'user.adminAccessLogs']);
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
        $payload = [
            'key' => 'something',
            'position' => 123,
            'title' => 'Something',
            'description' => 'Some random text...',
        ];
        $route = route('api-v1.moderations.sanctions-create');
        $response = $this->withToken($accessLog->token)->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_CREATED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereType('id', 'integer')
                ->whereType('key', 'string')
                ->whereType('position', 'integer')
                ->whereType('title', 'string')
                ->whereType('description', 'string')
                ->whereType('created_at', 'string')
                ->whereType('updated_at', 'string')
                ->etc()
            );
    });
});

describe('User Moderation Sanctions - Read - @GET /api/v1/moderations/sanctions', function () {
    it('succeeds user admin can read a sanction', function () {
        /** @var \Tests\TestCase $this */
        $admin = Admin::factory()->withAuth()->create();
        $admin->load(['user', 'user.adminAccessLogs']);
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
        $sanction = UserModerationSanction::first();
        $route = route('api-v1.moderations.sanctions-read', ['id' => $sanction->id]);
        $response = $this->withToken($accessLog->token)->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereType('id', 'integer')
                ->whereType('key', 'string')
                ->whereType('position', 'integer')
                ->whereType('title', 'string')
                ->whereType('description', 'string')
                ->whereType('created_at', 'string')
                ->whereType('updated_at', 'string')
                ->etc()
            );
    });
});

describe('User Moderation Sanctions - Update - @PATCH /api/v1/moderations/sanctions', function () {
    it('succeeds user admin can update an existing sanction', function () {
        /** @var \Tests\TestCase $this */
        $sanction = UserModerationSanction::factory()->create(['key' => 'spam']);
        $admin = Admin::factory()->withAuth()->create();
        $admin->load(['user', 'user.adminAccessLogs']);
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
        $payload = [
            'key' => 'spamming',
            'position' => 123,
            'title' => 'Spamming',
            'description' => 'Some random text...',
        ];
        $route = route('api-v1.moderations.sanctions-update', ['id' => $sanction->id]);
        $response = $this->withToken($accessLog->token)->patch($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_ACCEPTED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('id', fn ($id) => $id === $sanction->id)
                ->where('key', fn ($key) => $key === $payload['key'])
                ->where('position', fn ($position) => $position === $payload['position'])
                ->where('title', fn ($title) => $title === $payload['title'])
                ->where('description', fn ($description) => $description === $payload['description'])
                ->whereType('created_at', 'string')
                ->whereType('updated_at', 'string')
                ->etc()
            );
    });
});

describe('User Moderation Sanctions - Delete - @DELETE /api/v1/moderations/sanctions', function () {
    it('succeeds user admin can delete an existing sanction', function () {
        /** @var \Tests\TestCase $this */
        $sanction = UserModerationSanction::factory()->create();
        $admin = Admin::factory()->withAuth()->create();
        $admin->load(['user', 'user.adminAccessLogs']);
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
        $route = route('api-v1.moderations.sanctions-delete', ['id' => $sanction->id]);
        $response = $this->withToken($accessLog->token)->delete($route);
        $response->assertStatus(JsonResponse::HTTP_ACCEPTED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereType('message', 'string')
                ->where('id', fn ($id) => $id === $sanction->id)
                ->where('key', fn ($key) => $key === $sanction->key)
                ->etc()
            );
        // @phpstan-ignore-next-line
        $this->assertDatabaseMissing('users_moderation_categories', [
            'id' => $sanction->id,
        ]);
    });
});
