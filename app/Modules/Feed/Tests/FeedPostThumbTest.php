<?php

use App\Modules\Feed\Models\FeedPost;
use App\Modules\Feed\Models\FeedPostThumb;
use App\Modules\Member\Models\Member;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;

beforeEach(function () {
    Artisan::call('db:seed');

    $member = Member::factory()->withAuth()->create();
    FeedPost::factory()->create(['user_id' => $member->user_id]);
});

describe('Feed Post - Thumb Up - @POST /api/v1/feed/posts/{uid}/thumbs/up', function () {
    it('fails a not authenticated can thumb up a feed post', function () {
        /** @var \Tests\TestCase $this */
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
        /** @var \Tests\TestCase $this */
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
        /** @var \Tests\TestCase $this */
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

        $post = FeedPost::where('uid', $post['uid'])->first();
        $thumb = FeedPostThumb::query()
            ->where('user_id', $member->user->id)
            ->where('post_id', $post->id)
            ->first();
        expect($thumb)->not->toBeNull();
        expect($thumb->up)->toBeTruthy();
        expect($thumb->down)->toBeFalse();
    });
});

describe('Feed Post - Thumb Up - @POST /api/v1/feed/posts/{uid}/thumbs/up', function () {
    it('succeeds a user can thumb up a feed post and then by listing all post it sees its thumb up vote', function () {
        /** @var \Tests\TestCase $this */
        $member = Member::factory()->withAuth()->create();
        $member->load(['user', 'user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.feed.posts');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $post = $response->json()['result'][0];

        $route = route('api-v1.feed.post-thumbs-up-create', ['uid' => $post['uid']]);
        $response = $this->withToken($accessLog->token)->post($route, []);

        $route = route('api-v1.feed.posts');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('result', fn (AssertableJson $result) => $result
                    ->each(fn (AssertableJson $item) => $item
                        ->whereType('uid', 'integer')
                        ->has('user', fn (AssertableJson $user) => $user
                            ->whereType('uid', 'integer')
                            ->whereType('nickname', 'string')
                            ->etc()
                        )
                        // ...
                        ->whereType('title', 'string')
                        // ...
                        ->etc()
                    )
                    ->etc()
                )
                ->etc()
            );

        $items = collect($response->json('result'));
        $found = $items->firstWhere('uid', $post['uid']);
        expect($found)->not->toBeNull();
        expect((bool) ($found['is_thumb_up_by_me'] ?? false))->toBeTrue();
        expect((bool) ($found['is_thumb_down_by_me'] ?? false))->toBeFalse();
    });
});

describe('Feed Post - Thumb Down - @POST /api/v1/feed/posts/{uid}/thumbs/down', function () {
    it('succeeds an authenticated user can thumb down a feed post', function () {
        /** @var \Tests\TestCase $this */
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

        $post = FeedPost::where('uid', $post['uid'])->first();
        $thumb = FeedPostThumb::query()
            ->where('user_id', $member->user->id)
            ->where('post_id', $post->id)
            ->first();
        expect($thumb)->not->toBeNull();
        expect($thumb->up)->toBeFalse();
        expect($thumb->down)->toBeTruthy();
    });
});

describe('Feed Post - Thumb Down - @POST /api/v1/feed/posts/{uid}/thumbs/down', function () {
    it('succeeds a user can thumb down a feed post and then by listing all post it sees its thumb down vote', function () {
        /** @var \Tests\TestCase $this */
        $member = Member::factory()->withAuth()->create();
        $member->load(['user', 'user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.feed.posts');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $post = $response->json()['result'][0];

        $route = route('api-v1.feed.post-thumbs-down-create', ['uid' => $post['uid']]);
        $response = $this->withToken($accessLog->token)->post($route, []);

        $route = route('api-v1.feed.posts');
        $response = $this->withToken($accessLog->token)->get($route, []);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('result', fn (AssertableJson $result) => $result
                    ->each(fn (AssertableJson $item) => $item
                        ->whereType('uid', 'integer')
                        ->has('user', fn (AssertableJson $user) => $user
                            ->whereType('uid', 'integer')
                            ->whereType('nickname', 'string')
                            ->etc()
                        )
                        // ...
                        ->whereType('title', 'string')
                        // ...
                        ->etc()
                    )
                    ->etc()
                )
                ->etc()
            );

        $items = collect($response->json('result'));
        $found = $items->firstWhere('uid', $post['uid']);
        expect($found)->not->toBeNull();
        expect((bool) ($found['is_thumb_up_by_me'] ?? false))->toBeFalse();
        expect((bool) ($found['is_thumb_down_by_me'] ?? false))->toBeTrue();
    });
});
