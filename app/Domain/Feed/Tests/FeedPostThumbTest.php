<?php

/** @var \Tests\TestCase $this */

use App\Domain\Feed\Models\FeedPost;
use App\Domain\Member\Models\Member;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Artisan::call('db:seed');

    $member = Member::factory()->withAuth()->create();
    FeedPost::factory()->create(['user_id' => $member->user_id]);
});

describe('Feed Post - Thumb Up - @POST /api/v1/feed/posts/{uid}/thumbs/up', function () {
    it('fails a not authenticated can thumb up a feed post', function () {
        $route = route('api-v1.feed.posts');
        $response = $this->getJson($route);
        $post = $response->json()['result'][0];

        $route = route('api-v1.feed.post-thumbs-up-create', ['uid' => $post['uid']]);
        $response = $this->post($route);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'token_not_provided')
                ->etc()
            );
    });
});

describe('Feed Post - Thumb Down - @POST /api/v1/feed/posts/{uid}/thumbs/down', function () {
    it('fails a not authenticated can thumb down a feed post', function () {
        $route = route('api-v1.feed.posts');
        $response = $this->get($route, []);
        $post = $response->json()['result'][0];

        $route = route('api-v1.feed.post-thumbs-down-create', ['uid' => $post['uid']]);
        $response = $this->post($route);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'token_not_provided')
                ->etc()
            );
    });
});

describe('Feed Post - Thumb Up - @POST /api/v1/feed/posts/{uid}/thumbs/up', function () {
    it('succeeds an authenticated user can thumb up a feed post', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['user', 'user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.feed.posts');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $post = $response->json()['result'][0];

        $route = route('api-v1.feed.post-thumbs-up-create', ['uid' => $post['uid']]);
        $response = $this->withToken($accessLog->token)->post($route, []);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereType('message', 'string')
                ->has('post', fn (AssertableJson $post) => $post
                    ->whereType('uid', 'integer')
                    ->whereType('thumbs_up_count', 'integer')
                    ->whereType('thumbs_down_count', 'integer')
                    ->etc()
                )
                ->etc()
            );
    });
});

describe('Feed Post - Thumb Down - @POST /api/v1/feed/posts/{uid}/thumbs/down', function () {
    it('succeeds an authenticated user can thumb down a feed post', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['user', 'user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.feed.posts');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $post = $response->json()['result'][0];

        $route = route('api-v1.feed.post-thumbs-down-create', ['uid' => $post['uid']]);
        $response = $this->withToken($accessLog->token)->post($route, []);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereType('message', 'string')
                ->has('post', fn (AssertableJson $post) => $post
                    ->whereType('uid', 'integer')
                    ->whereType('thumbs_up_count', 'integer')
                    ->whereType('thumbs_down_count', 'integer')
                    ->etc()
                )
                ->etc()
            );
    });
});
