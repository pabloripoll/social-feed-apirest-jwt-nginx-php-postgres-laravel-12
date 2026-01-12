<?php

/** @var \Tests\TestCase $this */

use App\Domain\Feed\Models\FeedPost;
use App\Domain\Member\Models\Member;
use App\Domain\User\Models\UserModeration;
use App\Domain\User\Models\UserModerationCategory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;

beforeEach(function () {
    Artisan::call('db:seed');

    $member = Member::factory()->withAuth()->create();
    FeedPost::factory()->create(['user_id' => $member->user_id]);
});

describe('Feed Post Report - Create - @POST /api/v1/feed/posts/{uid}/reports', function () {
    it('fails a not authenticated can report a feed post', function () {
        $route = route('api-v1.feed.posts');
        $response = $this->getJson($route);
        $post = $response->json()['result'][0];

        $route = route('api-v1.feed.post-report-create', ['uid' => $post['uid']]);
        $response = $this->post($route);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'token_not_provided')
                ->etc()
            );
    });
});

describe('Feed Post Report - Create - @POST /api/v1/feed/posts/{uid}/reports', function () {
    it('fails a user cannot report a feed post with a unsafe report key', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['user', 'user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.feed.posts');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $post = $response->json()['result'][0];

        $route = route('api-v1.feed.post-report-create', ['uid' => $post['uid']]);
        $payload = [
            'key' => '()9n\"',
        ];
        $response = $this->withToken($accessLog->token)->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'key')
                ->etc()
            );
    });
});

describe('Feed Post Report - Create - @POST /api/v1/feed/posts/{uid}/reports', function () {
    it('fails a user cannot report a feed post with a wrong report key', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['user', 'user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.feed.posts');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $post = $response->json()['result'][0];

        $route = route('api-v1.feed.post-report-create', ['uid' => $post['uid']]);
        $payload = [
            'key' => 'anything',
        ];
        $response = $this->withToken($accessLog->token)->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'category_not_found')
                ->etc()
            );
    });
});

describe('Feed Post Report - Create - @POST /api/v1/feed/posts/{uid}/reports', function () {
    it('succeeds an authenticated user can report a feed post with a correct report key', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['user', 'user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.feed.posts');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $post = $response->json()['result'][0];

        UserModerationCategory::factory()->create();
        $category = UserModerationCategory::inRandomOrder()->first();

        $route = route('api-v1.feed.post-report-create', ['uid' => $post['uid']]);
        $payload = [
            'key' => $category->key,
        ];
        $response = $this->withToken($accessLog->token)->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_CREATED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->has('report', fn (AssertableJson $report) => $report
                    ->whereType('uid', 'integer')
                    ->whereType('category_key', 'string')
                    ->whereType('category_title', 'string')
                    ->whereType('created_at', 'string')
                    ->etc()
                )
                ->etc()
            );
    });
});

describe('Feed Post Report - Read - @GET /api/v1/feed/posts/{uid}/reports', function () {
    it('succeeds a user can report a feed post and then read it if available', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['user', 'user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.feed.posts');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $post = $response->json()['result'][0];

        UserModerationCategory::factory()->create();
        $category = UserModerationCategory::inRandomOrder()->first();

        $route = route('api-v1.feed.post-report-create', ['uid' => $post['uid']]);
        $payload = [
            'key' => $category->key,
        ];
        $response = $this->withToken($accessLog->token)->post($route, $payload);

        $route = route('api-v1.feed.post-report-read', ['uid' => $post['uid']]);
        $response = $this->withToken($accessLog->token)->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->has('report', fn (AssertableJson $report) => $report
                    ->whereType('uid', 'integer')
                    ->whereType('category_key', 'string')
                    ->whereType('category_title', 'string')
                    ->whereType('created_at', 'string')
                    ->etc()
                )
                ->etc()
            );
    });
});

describe('Feed Post Report - Update - @PATCH /api/v1/feed/posts/{uid}/reports', function () {
    it('fails a user cannot update a report because is no available to make changes for it', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['user', 'user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.feed.posts');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $post = $response->json()['result'][0];

        UserModerationCategory::factory()->create();
        $categories = UserModerationCategory::get();

        $route = route('api-v1.feed.post-report-create', ['uid' => $post['uid']]);
        $payload = [
            'key' => $categories[0]->key,
        ];
        $response = $this->withToken($accessLog->token)->post($route, $payload);
        $report = $response->json()['report'];

        UserModeration::where('uid', $report['uid'])->update([
            'in_review' => true,
            'in_review_since' => now(),
            'updated_at' => now(),
        ]);

        $route = route('api-v1.feed.post-report-update', ['uid' => $post['uid']]);
        $payload = [
            'key' => $categories[1]->key,
        ];
        $response = $this->withToken($accessLog->token)->patch($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'report_not_available')
                ->etc()
            );
    });
});

describe('Feed Post Report - Update - @PATCH /api/v1/feed/posts/{uid}/reports', function () {
    it('succeeds a user can report a feed post and then update it if available', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['user', 'user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.feed.posts');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $post = $response->json()['result'][0];

        UserModerationCategory::factory()->create();
        $categories = UserModerationCategory::get();

        $route = route('api-v1.feed.post-report-create', ['uid' => $post['uid']]);
        $payload = [
            'key' => $categories[0]->key,
        ];
        $response = $this->withToken($accessLog->token)->post($route, $payload);

        $route = route('api-v1.feed.post-report-update', ['uid' => $post['uid']]);
        $payload = [
            'key' => $categories[1]->key,
        ];
        $response = $this->withToken($accessLog->token)->patch($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_ACCEPTED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->has('report', fn (AssertableJson $report) => $report
                    ->whereType('uid', 'integer')
                    ->whereType('category_key', 'string')
                    ->whereType('category_title', 'string')
                    ->whereType('created_at', 'string')
                    ->whereType('updated_at', 'string')
                    ->etc()
                )
                ->etc()
            );
    });
});

describe('Feed Post Report - Delete - @DELETE /api/v1/feed/posts/{uid}/reports', function () {
    it('fails a user can delete its own report', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['user', 'user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.feed.posts');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $post = $response->json()['result'][0];

        UserModerationCategory::factory()->create();
        $categories = UserModerationCategory::get();

        $route = route('api-v1.feed.post-report-create', ['uid' => $post['uid']]);
        $payload = [
            'key' => $categories[0]->key,
        ];
        $response = $this->withToken($accessLog->token)->post($route, $payload);
        $report = $response->json()['report'];

        $route = route('api-v1.feed.post-report-delete', ['uid' => $post['uid']]);
        $response = $this->withToken($accessLog->token)->delete($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_ACCEPTED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->has('report', fn (AssertableJson $report) => $report
                    ->whereType('uid', 'integer')
                    ->whereType('category_key', 'string')
                    ->whereType('category_title', 'string')
                    ->whereType('created_at', 'string')
                    ->whereType('updated_at', 'string')
                    ->etc()
                )
                ->etc()
            );

        $legacyReport = UserModeration::where('uid', $report['uid'])->first();
        expect($legacyReport)->toBeNull();
    });
});
