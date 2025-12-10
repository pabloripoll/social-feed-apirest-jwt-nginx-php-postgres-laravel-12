<?php

/** @var \Tests\TestCase $this */

use App\Domain\Feed\Models\FeedCategory;
use App\Domain\Member\Models\Member;
use App\Support\Debug;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Artisan::call('db:seed');
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

describe('Member user on editing a feed post - @POST /api/v1/account/feed/posts', function () {
    it('fails when member send wrong request params to edit a feed post', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();
        $category = FeedCategory::where('key', 'example')->first();
        $payload = [
            'status' => 'broadcast',
            'category_id' => $category->id,
            'title' => 'Some example title',
            'article' => 'Lorem ipsum dolor sit amet...',
        ];

        $route = route('api-v1.account-feed.post-create');
        $response = $this->withToken($accessLog->token)->post($route, []);
        $post_uid = $response->json('post_uid');
        $failPayload = $payload;
        $failPayload['status'] = 'other';
        $route = route('api-v1.account-feed.post-edit', ['post_uid' => $post_uid]);
        $response = $this->withToken($accessLog->token)->put($route, $failPayload); Debug::log([$response->status(), $response->json()]);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('error', 'status')
                ->whereType('message', 'string')
                ->etc()
            );

        $route = route('api-v1.account-feed.post-create');
        $response = $this->withToken($accessLog->token)->post($route, []);
        $post_uid = $response->json('post_uid');
        $failPayload = $payload;
        $failPayload['category_id'] = 123;
        $route = route('api-v1.account-feed.post-edit', ['post_uid' => $post_uid]);
        $response = $this->withToken($accessLog->token)->put($route, $failPayload); Debug::log([$response->status(), $response->json()]);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('error', 'category_id')
                ->whereType('message', 'string')
                ->etc()
            );
    });
});

describe('Member user on editing a feed post as draft- @POST /api/v1/account/feed/posts', function () {
    it('succeeds when member edit a feed post for saving it as a draft', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();
        $route = route('api-v1.account-feed.post-create');
        $response = $this->withToken($accessLog->token)->post($route, []);
        $post_uid = (int) $response['post_uid'];
        $category = FeedCategory::where('key', 'example')->first();
        $params = [
            'post_uid' => $post_uid,
        ];
        $payload = [
            'status' => 'draft',
            'category_id' => $category->id,
            'title' => 'Some example title',
            'article' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque pen',
        ];
        $route = route('api-v1.account-feed.post-edit', $params);
        $response = $this->withToken($accessLog->token)->put($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_ACCEPTED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('post', fn (AssertableJson $postJson) => $postJson
                    ->where('uid', $post_uid)
                    ->where('category_id', $category->id)
                    ->where('is_draft', true)
                    ->where('is_active', false)
                    ->where('title', $payload['title'])
                    ->has('slug')
                    ->has('summary')
                    ->where('article', $payload['article'])
                    ->whereType('category_id', 'integer')
                    ->whereType('is_draft', 'boolean')
                    ->whereType('is_active', 'boolean')
                    ->whereType('slug', 'string')
                    ->whereType('summary', 'string')
                    ->etc()
                )
                ->etc()
            );
    });
});

describe('Member user on editing a feed post for broadcasting - @POST /api/v1/account/feed/posts', function () {
    it('succeeds when member edit a feed post for broadcasting', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();
        $route = route('api-v1.account-feed.post-create');
        $response = $this->withToken($accessLog->token)->post($route, []);
        $post_uid = (int) $response['post_uid'];
        $category = FeedCategory::where('key', 'example')->first();
        $params = [
            'post_uid' => $post_uid,
        ];
        $payload = [
            'status' => 'broadcast',
            'category_id' => $category->id,
            'title' => 'Some example title',
            'article' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque pen',
        ];
        $route = route('api-v1.account-feed.post-edit', $params);
        $response = $this->withToken($accessLog->token)->put($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_ACCEPTED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('post', fn (AssertableJson $postJson) => $postJson
                    ->where('uid', $post_uid)
                    ->where('category_id', $category->id)
                    ->where('is_draft', false)
                    ->where('is_active', true)
                    ->where('title', $payload['title'])
                    ->has('slug')
                    ->has('summary')
                    ->where('article', $payload['article'])
                    ->whereType('category_id', 'integer')
                    ->whereType('is_draft', 'boolean')
                    ->whereType('is_active', 'boolean')
                    ->whereType('slug', 'string')
                    ->whereType('summary', 'string')
                    ->etc()
                )
                ->etc()
            );
    });
});