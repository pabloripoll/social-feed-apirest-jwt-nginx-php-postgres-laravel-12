<?php

/** @var \Tests\TestCase $this */

use App\Domain\Feed\Models\FeedCategory;
use App\Domain\Member\Models\Member;
use App\Support\Debug;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Faker\Factory as FakerFactory;

beforeEach(function () {
    Artisan::call('db:seed');

    $faker = FakerFactory::create();
    $member = Member::factory()->withAuth()->create();
    $member->load(['user.memberAccessLogs']);
    $accessLog = $member->user->memberAccessLogs()->latest()->first();

    $route = route('api-v1.member-account.feed.post-create');
    $response = $this->withToken($accessLog->token)->post($route, []);

    $post_uid = $response['post_uid'];
    $category = FeedCategory::where('key', 'example')->first();

    $payload = [
        'status' => 'broadcast',
        'category_id' => $category->id,
        'title' => $faker->sentence(6),
        'article' => $faker->paragraphs(5, true),
    ];
    $route = route('api-v1.member-account.feed.post-edit', ['post_uid' => $post_uid]);
    $response = $this->withToken($accessLog->token)->put($route, $payload);
});

describe('Feed Post - Listing - @GET /api/v1/feed/posts', function () {
    it('succeeds a not authenticated user can list feed posts', function () {
        $route = route('api-v1.feed.posts');
        $response = $this->get($route, []);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('filters', fn (AssertableJson $filters) => $filters
                    ->whereType('categories', 'array')
                    ->has('sorting', fn (AssertableJson $sorting) => $sorting
                        ->where('recent', fn ($recent) => $recent === 'Recent')
                        ->where('oldest', fn ($oldest) => $oldest === 'Oldest')
                        ->where('thumbs-up', fn ($thumbsUp) => $thumbsUp === 'Thumbs Up')
                        ->where('thumbs-down', fn ($thumbsDown) => $thumbsDown === 'Thumbs Down')
                        ->etc()
                    )
                    ->etc()
                )
                ->has('listing', fn (AssertableJson $listing) => $listing
                    ->whereType('page', 'integer')
                    ->whereType('limit', 'integer')
                    ->whereType('total', 'integer')
                    ->whereType('pages', 'integer')
                    ->etc()
                )
                ->has('result', fn (AssertableJson $result) => $result
                    ->each(fn (AssertableJson $item) => $item
                        ->whereType('uid', 'integer')
                        ->has('user', fn (AssertableJson $user) => $user
                            ->whereType('uid', 'integer')
                            ->whereType('nickname', 'string')
                            ->etc()
                        )
                        //...
                        ->whereType('title', 'string')
                        //...
                        ->etc()
                    )
                    ->etc()
                )
                ->etc()
            );
    });
});

describe('Feed Post - Listing - @GET /api/v1/feed/posts', function () {
    it('succeeds an authenticated user can list feed posts', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.feed.posts');
        $response = $this->get($route, [], [
            'Authorization' => "Bearer $accessLog->token",
        ]);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('filters', fn (AssertableJson $filters) => $filters
                    ->whereType('categories', 'array')
                    ->has('sorting', fn (AssertableJson $sorting) => $sorting
                        ->where('recent', fn ($recent) => $recent === 'Recent')
                        ->where('oldest', fn ($oldest) => $oldest === 'Oldest')
                        ->where('thumbs-up', fn ($thumbsUp) => $thumbsUp === 'Thumbs Up')
                        ->where('thumbs-down', fn ($thumbsDown) => $thumbsDown === 'Thumbs Down')
                        ->etc()
                    )
                    ->etc()
                )
                ->has('listing', fn (AssertableJson $listing) => $listing
                    ->whereType('page', 'integer')
                    ->whereType('limit', 'integer')
                    ->whereType('total', 'integer')
                    ->whereType('pages', 'integer')
                    ->etc()
                )
                ->has('result', fn (AssertableJson $result) => $result
                    ->each(fn (AssertableJson $item) => $item
                        ->whereType('uid', 'integer')
                        ->has('user', fn (AssertableJson $user) => $user
                            ->whereType('uid', 'integer')
                            ->whereType('nickname', 'string')
                            ->etc()
                        )
                        //...
                        ->whereType('title', 'string')
                        //...
                        ->etc()
                    )
                    ->etc()
                )
                ->etc()
            );
    });
});

describe('Feed Post - Read - @GET /api/v1/feed/posts/{uid}', function () {
    it('succeeds a not authenticated user can read a feed post', function () {
        $route = route('api-v1.feed.posts');
        $response = $this->get($route, []);

        $post = $response->json()['result'][0];

        $route = route('api-v1.feed.post-read', ['uid' => $post['uid']]);
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereType('message', 'string')
                ->has('post', fn (AssertableJson $post) => $post
                    ->whereType('uid', 'integer')
                    ->has('user', fn (AssertableJson $user) => $user
                        ->whereType('uid', 'integer')
                        ->whereType('nickname', 'string')
                        ->etc()
                    )
                    //...
                    ->whereType('title', 'string')
                    //...
                    ->etc()
                )
                ->etc()
            );
    });
});

describe('Feed Post - Read - @GET /api/v1/feed/posts/{uid}', function () {
    it('succeeds an authenticated user can read a feed post', function () {
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.feed.posts');
        $response = $this->get($route, [], [
            'Authorization' => "Bearer $accessLog->token",
        ]);

        $post = $response->json()['result'][0];

        $route = route('api-v1.feed.post-read', ['uid' => $post['uid']]);
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->whereType('message', 'string')
                ->has('post', fn (AssertableJson $post) => $post
                    ->whereType('uid', 'integer')
                    ->has('user', fn (AssertableJson $user) => $user
                        ->whereType('uid', 'integer')
                        ->whereType('nickname', 'string')
                        ->etc()
                    )
                    //...
                    ->whereType('title', 'string')
                    //...
                    ->etc()
                )
                ->etc()
            );
    });
});
