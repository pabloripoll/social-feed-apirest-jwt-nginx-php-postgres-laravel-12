<?php

/** @var \Tests\TestCase $this */

use App\Domain\Feed\Models\FeedCategory;
use App\Domain\Geo\Models\GeoRegion;
use App\Domain\Member\Models\Member;
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

describe('Member user on editing a feed post - @PUT /api/v1/account/feed/posts/{post_uid}', function () {
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
        $response = $this->withToken($accessLog->token)->put($route, $failPayload);
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
        $response = $this->withToken($accessLog->token)->put($route, $failPayload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('error', 'category_id')
                ->whereType('message', 'string')
                ->etc()
            );
    });
});

describe('Member user on editing a feed post as draft- @PUT /api/v1/account/feed/posts/{post_uid}', function () {
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

describe('Member user on editing a feed post for broadcasting - @PUT /api/v1/account/feed/posts/{post_uid}', function () {
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

describe('Member user on reading a feed post - @GET /api/v1/account/feed/posts/{post_uid}', function () {
    it('succeeds member can read its own feed post', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $region = GeoRegion::latest()->first();

        $member->region_id = $region->id;

        $route = route('api-v1.account-feed.post-create');
        $response = $this->withToken($accessLog->token)->post($route, []);
        $post_uid = $response['post_uid'];
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

        $route = route('api-v1.account-feed.post-read', $params);
        $response = $this->withToken($accessLog->token)->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('post', fn (AssertableJson $postJson) => $postJson
                    ->where('uid', $post_uid)
                    ->has('user', fn (AssertableJson $userJson) => $userJson
                        ->whereType('uid', 'integer')
                        ->where('uid', (int) $member->uid)
                        ->whereType('nickname', 'string')
                        ->etc()
                    )
                    ->whereType('continent_id', 'integer')
                    ->whereType('continent_name', 'string')
                    ->whereType('region_id', 'integer')
                    ->whereType('region_name', 'string')
                    ->where('category_id', $category->id)
                    ->whereType('category_id', 'integer')
                    ->where('is_draft', false)
                    ->whereType('is_draft', 'boolean')
                    ->where('is_active', true)
                    ->whereType('is_active', 'boolean')
                    ->where('is_banned', false)
                    ->whereType('is_banned', 'boolean')
                    ->where('title', $payload['title'])
                    ->whereType('title', 'string')
                    ->has('slug')
                    ->whereType('slug', 'string')
                    ->has('summary')
                    ->whereType('summary', 'string')
                    ->where('article', $payload['article'])
                    ->whereType('article', 'string')
                    ->etc()
                )
                ->etc()
            );
    });
});

describe('Member user on reading a feed sketch post - @GET /api/v1/account/feed/posts/sketch', function () {
    it('succeeds member can read its own latest feed sketched post', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.account-feed.post-create');
        $response = $this->withToken($accessLog->token)->post($route, []);
        $post_uid = $response['post_uid'];

        $route = route('api-v1.account-feed.post-read-sketch');
        $response = $this->withToken($accessLog->token)->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('post', fn (AssertableJson $postJson) => $postJson
                    ->where('uid', $post_uid)
                    ->has('user', fn (AssertableJson $userJson) => $userJson
                        ->whereType('uid', 'integer')
                        ->where('uid', (int) $member->uid)
                        ->whereType('nickname', 'string')
                        ->etc()
                    )
                    ->has('continent_id')
                    ->has('continent_name')
                    ->has('region_id')
                    ->has('region_name')
                    ->has('category_id')
                    ->where('is_sketch', true)
                    ->where('is_draft', false)
                    ->where('is_active', false)
                    ->where('is_banned', false)
                    ->has('title')
                    ->has('slug')
                    ->has('summary')
                    ->has('article')
                    ->etc()
                )
                ->etc()
            );
    });
});