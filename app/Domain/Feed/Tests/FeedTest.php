<?php

use App\Domain\Member\Models\Member;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;

beforeEach(function () {
    Artisan::call('db:seed');
});

describe('Feed Post - Report Types - @GET /api/v1/feed/reports/types', function () {
    it('succeeds a not authenticated user can list feed post report types', function () {
        /** @var \Tests\TestCase $this */
        $route = route('api-v1.feed.report-types');
        $response = $this->get($route, []);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->each(fn (AssertableJson $item) => $item
                    ->whereType('id', 'integer')
                    ->whereType('key', 'string')
                    ->whereType('title', 'string')
                    ->etc()
                )
                ->etc()
            );
    });
});

describe('Feed Post - Report Types - @GET /api/v1/feed/reports/types', function () {
    it('succeeds an authenticated user can list feed post report types', function () {
        /** @var \Tests\TestCase $this */
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.feed.report-types');
        $response = $this->get($route, [
            'Authorization' => "Bearer $accessLog->token",
        ]);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->each(fn (AssertableJson $item) => $item
                    ->whereType('id', 'integer')
                    ->whereType('key', 'string')
                    ->whereType('title', 'string')
                    ->etc()
                )
                ->etc()
            );
    });
});

describe('Feed Post - Categories - @GET /api/v1/feed/categories', function () {
    it('succeeds a not authenticated user can list feed post categories', function () {
        /** @var \Tests\TestCase $this */
        $route = route('api-v1.feed.categories');
        $response = $this->get($route, []);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->each(fn (AssertableJson $item) => $item
                    ->whereType('id', 'integer')
                    ->whereType('key', 'string')
                    ->whereType('title', 'string')
                    ->etc()
                )
                ->etc()
            );
    });
});

describe('Feed Post - Categories - @GET /api/v1/feed/categories', function () {
    it('succeeds an authenticated user can list feed post categories', function () {
        /** @var \Tests\TestCase $this */
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.feed.categories');
        $response = $this->get($route, [
            'Authorization' => "Bearer $accessLog->token",
        ]);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->each(fn (AssertableJson $item) => $item
                    ->whereType('id', 'integer')
                    ->whereType('key', 'string')
                    ->whereType('title', 'string')
                    ->etc()
                )
                ->etc()
            );
    });
});
