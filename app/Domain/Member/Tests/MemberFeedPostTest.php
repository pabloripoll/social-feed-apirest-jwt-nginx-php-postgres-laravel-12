<?php

/** @var \Tests\TestCase $this */

use App\Domain\Feed\Models\FeedCategory;
use App\Domain\Member\Models\Member;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;

beforeEach(function () {
    Artisan::call('db:seed');
});

describe('Member Feed Post - Not authenticated - @POST /api/v1/account/feed/posts', function () {
    it('succeeds a not authenticated user cannot access to protected route without header token', function () {
        /** @var \Tests\TestCase $this */
        $route = route('api-v1.member-account.feed.post-create');
        $response = $this->post($route, []);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('message', fn ($message) => is_string($message))
                ->where('error', fn ($error) => $error === 'token_not_provided')
                ->etc()
            );
    });
});

describe('Member Feed Post - wrong authenticated - @POST /api/v1/account/feed/posts', function () {
    it('succeeds a user cannot access to protected route with an invalid token', function () {
        /** @var \Tests\TestCase $this */
        $route = route('api-v1.member-account.feed.post-create');
        $fakeToken = fakeJWT();
        $response = $this->post($route, [], [
            'Authorization' => 'Bearer '.$fakeToken,
        ]);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('message', fn ($message) => is_string($message))
                ->where('error', fn ($error) => $error === 'token_invalid')
                ->etc()
            );
    });
});

describe('Member Feed Post - create - @POST /api/v1/account/feed/posts', function () {
    it('succeeds authenticated member user can create a feed post', function () {
        /** @var \Tests\TestCase $this */
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.member-account.feed.post-create');
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

describe('Member Feed Post - edit fail - @PUT /api/v1/account/feed/posts/{post_uid}', function () {
    it('fails when member send wrong request params to edit a feed post', function () {
        /** @var \Tests\TestCase $this */
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

        $route = route('api-v1.member-account.feed.post-create');
        $response = $this->withToken($accessLog->token)->post($route, []);
        $post_uid = $response->json('post_uid');

        $failPayload = $payload;
        $failPayload['status'] = 'other';
        $route = route('api-v1.member-account.feed.post-edit', ['post_uid' => $post_uid]);
        $response = $this->withToken($accessLog->token)->put($route, $failPayload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('error', 'status')
                ->whereType('message', 'string')
                ->etc()
            );

        $route = route('api-v1.member-account.feed.post-create');
        $response = $this->withToken($accessLog->token)->post($route, []);
        $post_uid = $response->json('post_uid');

        $failPayload = $payload;
        $failPayload['category_id'] = 123;
        $route = route('api-v1.member-account.feed.post-edit', ['post_uid' => $post_uid]);
        $response = $this->withToken($accessLog->token)->put($route, $failPayload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('error', 'category_id')
                ->whereType('message', 'string')
                ->etc()
            );
    });
});

describe('Member Feed Post - edit as draft - @PUT /api/v1/account/feed/posts/{post_uid}', function () {
    it('succeeds when member edit a feed post for saving it as a draft', function () {
        /** @var \Tests\TestCase $this */
        $faker = FakerFactory::create();
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.member-account.feed.post-create');
        $response = $this->withToken($accessLog->token)->post($route, []);
        $post_uid = (int) $response['post_uid'];

        $category = FeedCategory::where('key', 'example')->first();

        $payload = [
            'status' => 'draft',
            'category_id' => $category->id,
            'title' => $faker->sentence(6),
            'article' => $faker->paragraphs(5, true),
        ];
        $route = route('api-v1.member-account.feed.post-edit', ['post_uid' => $post_uid]);
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

describe('Member Feed Post - edit for active broadcasting - @PUT /api/v1/account/feed/posts/{post_uid}', function () {
    it('succeeds when member edit a feed post for broadcasting', function () {
        /** @var \Tests\TestCase $this */
        $faker = FakerFactory::create();
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.member-account.feed.post-create');
        $response = $this->withToken($accessLog->token)->post($route, []);

        $post_uid = (int) $response['post_uid'];
        $category = FeedCategory::where('key', 'example')->first();

        $payload = [
            'status' => 'broadcast',
            'category_id' => $category->id,
            'title' => $faker->sentence(6),
            'article' => $faker->paragraphs(5, true),
        ];
        $route = route('api-v1.member-account.feed.post-edit', ['post_uid' => $post_uid]);
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

describe('Member Feed Post - read - @GET /api/v1/account/feed/posts/{post_uid}', function () {
    it('succeeds member can read its own feed post', function () {
        /** @var \Tests\TestCase $this */
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

        $route = route('api-v1.member-account.feed.post-read', ['post_uid' => $post_uid]);
        $response = $this->withToken($accessLog->token)->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('post', fn (AssertableJson $postJson) => $postJson
                    ->where('uid', $post_uid)
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

describe('Member Feed Post - read sketch - @GET /api/v1/account/feed/posts/sketch', function () {
    it('succeeds member can read its own latest feed sketched post', function () {
        /** @var \Tests\TestCase $this */
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.member-account.feed.post-create');
        $response = $this->withToken($accessLog->token)->post($route, []);
        $post_uid = $response['post_uid'];

        $route = route('api-v1.member-account.feed.post-read-sketch');
        $response = $this->withToken($accessLog->token)->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('post', fn (AssertableJson $postJson) => $postJson
                    ->where('uid', $post_uid)
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

describe('Member Feed Post - update fail - @PATCH /api/v1/account/feed/posts/{post_uid}', function () {
    it('fails when member send wrong request params to update a feed post', function () {
        /** @var \Tests\TestCase $this */
        $faker = FakerFactory::create();
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.member-account.feed.post-create');
        $response = $this->withToken($accessLog->token)->post($route, []);

        $post_uid = (int) $response['post_uid'];
        $category = FeedCategory::where('key', 'example')->first();

        $payload = [
            'status' => 'broadcast',
            'category_id' => $category->id,
            'title' => $faker->sentence(6),
            'article' => $faker->paragraphs(5, true),
        ];
        $route = route('api-v1.member-account.feed.post-edit', ['post_uid' => $post_uid]);
        $response = $this->withToken($accessLog->token)->put($route, $payload);
        $post_uid = $response->json()['post']['uid'];

        $failPayload = $payload;
        $failPayload['status'] = 'other';
        $route = route('api-v1.member-account.feed.post-update', ['post_uid' => $post_uid]);
        $response = $this->withToken($accessLog->token)->patch($route, $failPayload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('error', 'status')
                ->whereType('message', 'string')
                ->etc()
            );

        $failPayload = $payload;
        $failPayload['category_id'] = 123;
        $route = route('api-v1.member-account.feed.post-update', ['post_uid' => $post_uid]);
        $response = $this->withToken($accessLog->token)->patch($route, $failPayload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('error', 'category_id')
                ->whereType('message', 'string')
                ->etc()
            );
    });
});

describe('Member Feed Post - update to draft - @PATCH /api/v1/account/feed/posts/{post_uid}', function () {
    it('succeeds when member update a feed post from active to draft', function () {
        /** @var \Tests\TestCase $this */
        $faker = FakerFactory::create();
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.member-account.feed.post-create');
        $response = $this->withToken($accessLog->token)->post($route, []);

        $post_uid = (int) $response['post_uid'];
        $category = FeedCategory::where('key', 'example')->first();

        $payload = [
            'status' => 'broadcast',
            'category_id' => $category->id,
            'title' => $faker->sentence(6),
            'article' => $faker->paragraphs(5, true),
        ];
        $route = route('api-v1.member-account.feed.post-edit', ['post_uid' => $post_uid]);
        $response = $this->withToken($accessLog->token)->put($route, $payload);

        $payload['status'] = 'draft';
        $route = route('api-v1.member-account.feed.post-update', ['post_uid' => $post_uid]);
        $response = $this->withToken($accessLog->token)->patch($route, $payload);
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

describe('Member Feed Post - update to broadcast - @PATCH /api/v1/account/feed/posts/{post_uid}', function () {
    it('succeeds when member update a feed post from draft to active', function () {
        /** @var \Tests\TestCase $this */
        $faker = FakerFactory::create();
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.member-account.feed.post-create');
        $response = $this->withToken($accessLog->token)->post($route, []);

        $post_uid = (int) $response['post_uid'];
        $category = FeedCategory::where('key', 'example')->first();

        $payload = [
            'status' => 'draft',
            'category_id' => $category->id,
            'title' => $faker->sentence(6),
            'article' => $faker->paragraphs(5, true),
        ];
        $route = route('api-v1.member-account.feed.post-edit', ['post_uid' => $post_uid]);
        $response = $this->withToken($accessLog->token)->put($route, $payload);

        $payload['status'] = 'broadcast';
        $route = route('api-v1.member-account.feed.post-update', ['post_uid' => $post_uid]);
        $response = $this->withToken($accessLog->token)->patch($route, $payload);
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

describe('Member Feed Post - delete - @DELETE /api/v1/account/feed/posts/{post_uid}', function () {
    it('succeeds when member delete a feed post', function () {
        /** @var \Tests\TestCase $this */
        $faker = FakerFactory::create();
        $member = Member::factory()->withAuth()->create();
        $member->load(['user.memberAccessLogs']);
        $accessLog = $member->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.member-account.feed.post-create');
        $response = $this->withToken($accessLog->token)->post($route, []);
        $post_uid = $response->json()['post_uid'];

        $category = FeedCategory::where('key', 'example')->first();
        $payload = [
            'status' => 'draft',
            'category_id' => $category->id,
            'title' => $faker->sentence(6),
            'article' => $faker->paragraphs(5, true),
        ];
        $route = route('api-v1.member-account.feed.post-edit', ['post_uid' => $post_uid]);
        $response = $this->withToken($accessLog->token)->put($route, $payload);

        $route = route('api-v1.member-account.feed.post-delete', ['post_uid' => $post_uid]);
        $response = $this->withToken($accessLog->token)->delete($route);
        $response->assertStatus(JsonResponse::HTTP_OK);

        $route = route('api-v1.member-account.feed.post-read', ['post_uid' => $post_uid]);
        $response = $this->withToken($accessLog->token)->get($route);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    });
});
