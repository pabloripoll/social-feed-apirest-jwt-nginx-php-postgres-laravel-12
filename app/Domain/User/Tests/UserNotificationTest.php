<?php

/** @var \Tests\TestCase $this */

use App\Domain\Feed\Models\FeedCategory;
use App\Domain\Feed\Models\FeedPost;
use App\Domain\Member\Models\Member;
use App\Domain\Member\Models\MemberFollower;
use App\Domain\User\Models\UserNotificationType;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;

beforeEach(function () {
    Artisan::call('db:seed');
});

describe('Member Notification - New Follower - @GET /api/v1/account/notifications', function () {
    it('succeeds a member receives the notification of the following action from another member', function () {
        /** @var \Tests\TestCase $this */
        $memberOne = Member::factory()->withAuth()->create();
        $memberOne->load(['user', 'profile', 'user.memberAccessLogs']);
        $memberOneAccess = $memberOne->user->memberAccessLogs()->latest()->first();

        $memberTwo = Member::factory()->withAuth()->create();
        $memberTwo->load(['user', 'profile', 'user.memberAccessLogs']);
        $memberTwoAccess = $memberTwo->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.members.profile-follow', ['member_uid' => $memberOne->uid]);
        $response = $this->withToken($memberTwoAccess->token)->post($route, []);
        $response->assertStatus(JsonResponse::HTTP_CREATED);

        $notificationType = UserNotificationType::where('key', 'new-follower')->first();

        $route = route('api-v1.member-account.notifications-listing');
        $response = $this->withToken($memberOneAccess->token)->get($route, []);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('listing', fn (AssertableJson $listing) => $listing
                    ->whereType('page', 'integer')
                    ->whereType('limit', 'integer')
                    ->whereType('total', 'integer')
                    ->whereType('pages', 'integer')
                    ->whereType('first_page', 'string')
                    ->whereType('last_page', 'string')
                    ->etc()
                )
                ->has('result', fn (AssertableJson $result) => $result
                    ->each(fn (AssertableJson $item) => $item
                        ->whereType('uid', 'integer')
                        ->where('type_id', fn ($type_id) => $type_id === $notificationType->id)
                        ->whereType('notify_count', 'integer')
                        ->whereType('title', 'string')
                        ->whereType('summary', 'string')
                        ->whereType('created_at', 'string')
                        ->whereType('updated_at', 'string')
                        ->etc()
                    )
                    ->etc()
                )
                ->etc()
            );
    });
});

describe('Member Notification - New Feed Post - @GET /api/v1/account/notifications', function () {
    it('succeeds a member receives the notification when a new feed post has been from broadcasted from a following member', function () {
        /** @var \Tests\TestCase $this */
        $memberOne = Member::factory()->withAuth()->create();
        $memberOne->load(['user', 'profile', 'user.memberAccessLogs']);
        $memberOneAccess = $memberOne->user->memberAccessLogs()->latest()->first();

        $memberTwo = Member::factory()->withAuth()->create();
        $memberTwo->load(['user', 'profile', 'user.memberAccessLogs']);
        $memberTwoAccess = $memberTwo->user->memberAccessLogs()->latest()->first();

        $follower = new MemberFollower;
        $follower->user_id = $memberTwo->user_id;
        $follower->following_user_id = $memberOne->user_id;
        $follower->save();

        $route = route('api-v1.member-account.feed.post-create');
        $response = $this->withToken($memberOneAccess->token)->post($route, []);
        $post_uid = $response->json()['post_uid'];

        $faker = FakerFactory::create();
        $category = FeedCategory::where('key', 'example')->first();
        $payload = [
            'status' => 'broadcast',
            'category_id' => $category->id,
            'title' => $faker->sentence(6),
            'article' => $faker->paragraphs(5, true),
        ];
        $route = route('api-v1.member-account.feed.post-edit', ['post_uid' => $post_uid]);
        $response = $this->withToken($memberOneAccess->token)->put($route, $payload);

        $notificationType = UserNotificationType::where('key', 'new-feed-post')->first();

        $route = route('api-v1.member-account.notifications-listing');
        $response = $this->withToken($memberTwoAccess->token)->get($route, []);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('listing', fn (AssertableJson $listing) => $listing
                    ->whereType('page', 'integer')
                    ->whereType('limit', 'integer')
                    ->whereType('total', 'integer')
                    ->whereType('pages', 'integer')
                    ->whereType('first_page', 'string')
                    ->whereType('last_page', 'string')
                    ->etc()
                )
                ->has('result', fn (AssertableJson $result) => $result
                    ->each(fn (AssertableJson $item) => $item
                        ->whereType('uid', 'integer')
                        ->where('type_id', fn ($type_id) => $type_id === $notificationType->id)
                        ->whereType('notify_count', 'integer')
                        ->whereType('title', 'string')
                        ->whereType('summary', 'string')
                        ->whereType('created_at', 'string')
                        ->whereType('updated_at', 'string')
                        ->etc()
                    )
                    ->etc()
                )
                ->etc()
            );
    });
});

describe('Member Notification - New Feed Post Thumb-Up - @GET /api/v1/account/notifications', function () {
    it('succeeds a member receives the notification when member gave a thumb-up vote', function () {
        /** @var \Tests\TestCase $this */
        $memberOne = Member::factory()->withAuth()->create();
        $memberOne->load(['user', 'profile', 'user.memberAccessLogs']);
        $memberOneAccess = $memberOne->user->memberAccessLogs()->latest()->first();

        $post = FeedPost::factory()->create(['user_id' => $memberOne->user_id]);

        $memberTwo = Member::factory()->withAuth()->create();
        $memberTwo->load(['user', 'profile', 'user.memberAccessLogs']);
        $memberTwoAccess = $memberTwo->user->memberAccessLogs()->latest()->first();

        $route = route('api-v1.feed.post-thumbs-up-create', ['uid' => $post['uid']]);
        $response = $this->withToken($memberTwoAccess->token)->post($route, []);

        $notificationType = UserNotificationType::where('key', 'new-feed-post-thumb-up')->first();

        $route = route('api-v1.member-account.notifications-listing');
        $response = $this->withToken($memberOneAccess->token)->get($route, []);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('listing', fn (AssertableJson $listing) => $listing
                    ->whereType('page', 'integer')
                    ->whereType('limit', 'integer')
                    ->whereType('total', 'integer')
                    ->whereType('pages', 'integer')
                    ->whereType('first_page', 'string')
                    ->whereType('last_page', 'string')
                    ->etc()
                )
                ->has('result', fn (AssertableJson $result) => $result
                    ->each(fn (AssertableJson $item) => $item
                        ->whereType('uid', 'integer')
                        ->where('type_id', fn ($type_id) => $type_id === $notificationType->id)
                        ->whereType('notify_count', 'integer')
                        ->whereType('title', 'string')
                        ->whereType('summary', 'string')
                        ->whereType('created_at', 'string')
                        ->whereType('updated_at', 'string')
                        ->etc()
                    )
                    ->etc()
                )
                ->etc()
            );
    });
});
