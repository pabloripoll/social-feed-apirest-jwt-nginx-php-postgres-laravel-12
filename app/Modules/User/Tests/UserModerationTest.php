<?php

/** @var \Tests\TestCase $this */

use App\Modules\Admin\Models\Admin;
use App\Modules\Member\Models\Member;
use App\Modules\User\Database\Factories\UserModerationFactory;
use App\Modules\User\Models\UserModeration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;

beforeEach(function () {
    Artisan::call('db:seed');
});

describe('User Moderation - Listing - @GET /api/v1/moderations', function () {
    it('fails a not authenticated user nor any member can access to any of user moderation management routes', function () {
        /** @var \Tests\TestCase $this */
        $route = route('api-v1.moderations.listing');
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
        $route = route('api-v1.moderations.listing');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'forbidden_access')
                ->etc()
            );
        $route = route('api-v1.moderations.filters');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'forbidden_access')
                ->etc()
            );
    });
});

describe('User Moderation - Listing Filters - @GET /api/v1/moderations/filters', function () {
    it('succeeds user admin can request listing filters', function () {
        /** @var \Tests\TestCase $this */
        $admin = Admin::factory()->withAuth()->create();
        $admin->load(['user', 'user.adminAccessLogs']);
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
        $route = route('api-v1.moderations.filters');
        $response = $this->withToken($accessLog->token)->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('categories', fn (AssertableJson $json) => $json
                    ->each(fn (AssertableJson $json) => $json
                        ->has('key')
                        ->has('title')
                        ->whereType('key', 'string')
                        ->whereType('title', 'string')
                    )
                )
                ->has('sanctions', fn (AssertableJson $json) => $json
                    ->each(fn (AssertableJson $json) => $json
                        ->has('key')
                        ->has('title')
                        ->whereType('key', 'string')
                        ->whereType('title', 'string')
                    )
                )
                ->has('status', fn (AssertableJson $json) => $json
                    ->where('reviewing', 'Reviewing')
                    ->where('resolved', 'Resolved')
                    ->where('closed', 'Closed')
                )
                ->has('sorting', fn (AssertableJson $json) => $json
                    ->where('recent', 'Recent')
                    ->where('oldest', 'Oldest')
                )
                ->has('moderator', fn (AssertableJson $json) => $json
                    ->where('me', 'Moderated by me')
                    ->where('all', 'All moderators')
                )
                ->etc()
            );
    });
});

describe('User Moderation - Listing - @GET /api/v1/moderations', function () {
    it('succeeds any user admin can list moderations', function () {
        /** @var \Tests\TestCase $this */
        $admin = Admin::factory()->withAuth()->create();
        $admin->load(['user', 'user.adminAccessLogs']);
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
        UserModeration::factory()::createMixedStates(); // default 1 moderation for each case
        $route = route('api-v1.moderations.listing');
        $response = $this->withToken($accessLog->token)->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('filters', fn (AssertableJson $json) => $json
                    ->has('categories', fn (AssertableJson $json) => $json
                        ->each(fn (AssertableJson $json) => $json
                            ->has('key')
                            ->has('title')
                            ->whereType('key', 'string')
                            ->whereType('title', 'string')
                        )
                    )
                    ->has('sanctions', fn (AssertableJson $json) => $json
                        ->each(fn (AssertableJson $json) => $json
                            ->has('key')
                            ->has('title')
                            ->whereType('key', 'string')
                            ->whereType('title', 'string')
                        )
                    )
                    ->has('status', fn (AssertableJson $json) => $json
                        ->where('reviewing', 'Reviewing')
                        ->where('resolved', 'Resolved')
                        ->where('closed', 'Closed')
                    )
                    ->has('sorting', fn (AssertableJson $json) => $json
                        ->where('recent', 'Recent')
                        ->where('oldest', 'Oldest')
                    )
                    ->has('moderator', fn (AssertableJson $json) => $json
                        ->where('me', 'Moderated by me')
                        ->where('all', 'All moderators')
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
        expect($response->json('result'))->toHaveCount(5);
    });
});

describe('User Moderation - Listing by Category - @GET /api/v1/moderations', function () {
    it('succeeds any user admin can list moderations by category', function () {
        /** @var \Tests\TestCase $this */
        $admin = Admin::factory()->withAuth()->create();
        $admin->load(['user', 'user.adminAccessLogs']);
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
        UserModerationFactory::createMixedStates(); // default 1 moderation for each case
        $sample = UserModeration::query()
            ->with(['category'])
            ->select(['id', 'category_id'])
            ->first();
        $route = route('api-v1.moderations.listing', ['category' => $sample->category->key]);
        $response = $this->withToken($accessLog->token)->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK);
        expect($response->json('result'))->toHaveCount(1);
        expect($response->json('listing')['total'])->toBeGreaterThanOrEqual(1);
    });
});
