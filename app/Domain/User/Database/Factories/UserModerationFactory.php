<?php

namespace App\Domain\User\Database\Factories;

use App\Domain\Feed\Models\FeedPost;
use App\Domain\Admin\Models\Admin;
use App\Domain\Member\Models\Member;
use App\Domain\User\Models\User;
use App\Domain\User\Models\UserModeration;
use App\Domain\User\Models\UserModerationCategory;
use App\Domain\User\Models\UserModerationSanction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\User\Models\UserModeration>
 */
class UserModerationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = UserModeration::class;

    /**
     * Define the model's default state (newly opened moderation).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Create members which auto-create User and MemberProfile
        $reportedMember = Member::factory()->create();
        $reporterMember = Member::factory()->create();

        return [
            'user_id' => $reportedMember->user_id,
            'reporter_user_id' => $reporterMember->user_id,
            'moderator_user_id' => null,
            'is_opened' => true,
            'in_review' => false,
            'in_review_since' => null,
            'is_resolved' => false,
            'resolved_at' => null,
            'is_closed' => false,
            'closed_at' => null,
            'category_id' => UserModerationCategory::factory(),
            'sanction_id' => null,
            'has_sanction_active' => false,
            'sanction_expires_at' => null,
            'feed_post_id' => $this->faker->optional(0.7)->randomElement([
                FeedPost::factory(),
                null,
            ]),
        ];
    }

    /**
     * Indicate that the moderation is in review (creates admin moderator).
     */
    public function inReview(?Admin $admin = null): static
    {
        return $this->state(function (array $attributes) use ($admin) {
            $moderator = $admin ?? Admin::factory()->create();

            return [
                'is_opened' => true,
                'in_review' => true,
                'in_review_since' => $this->faker->dateTimeBetween('-7 days', 'now'),
                'moderator_user_id' => $moderator->user_id,
                'is_resolved' => false,
                'is_closed' => false,
            ];
        });
    }

    /**
     * Indicate that the moderation is resolved (creates admin moderator).
     */
    public function resolved(?Admin $admin = null): static
    {
        $reviewedAt = $this->faker->dateTimeBetween('-30 days', '-1 day');
        $resolvedAt = $this->faker->dateTimeBetween($reviewedAt, 'now');

        return $this->state(function (array $attributes) use ($admin, $reviewedAt, $resolvedAt) {
            $moderator = $admin ?? Admin::factory()->create();

            return [
                'is_opened' => true,
                'in_review' => false,
                'in_review_since' => $reviewedAt,
                'moderator_user_id' => $moderator->user_id,
                'is_resolved' => true,
                'resolved_at' => $resolvedAt,
                'is_closed' => false,
                'closed_at' => null,
            ];
        });
    }

    /**
     * Indicate that the moderation is closed (creates admin moderator).
     */
    public function closed(?Admin $admin = null): static
    {
        $reviewedAt = $this->faker->dateTimeBetween('-60 days', '-30 days');
        $resolvedAt = $this->faker->dateTimeBetween($reviewedAt, '-15 days');
        $closedAt = $this->faker->dateTimeBetween($resolvedAt, 'now');

        return $this->state(function (array $attributes) use ($admin, $reviewedAt, $resolvedAt, $closedAt) {
            $moderator = $admin ?? Admin::factory()->create();

            return [
                'is_opened' => true,
                'in_review' => false,
                'in_review_since' => $reviewedAt,
                'moderator_user_id' => $moderator->user_id,
                'is_resolved' => true,
                'resolved_at' => $resolvedAt,
                'is_closed' => true,
                'closed_at' => $closedAt,
            ];
        });
    }

    /**
     * Indicate that the moderation has an active sanction.
     */
    public function withActiveSanction(? UserModerationSanction $sanction = null, ?string $expiresAt = null): static
    {
        return $this->state(function (array $attributes) use ($sanction, $expiresAt) {
            $sanctionModel = $sanction ?? UserModerationSanction::factory()->create();

            return [
                'sanction_id' => $sanctionModel->id,
                'has_sanction_active' => true,
                'sanction_expires_at' => $expiresAt ?? $this->faker->dateTimeBetween('now', '+90 days'),
            ];
        });
    }

    /**
     * Indicate that the moderation has an expired sanction.
     */
    public function withExpiredSanction(?UserModerationSanction $sanction = null): static
    {
        return $this->state(function (array $attributes) use ($sanction) {
            $sanctionModel = $sanction ?? UserModerationSanction::factory()->create();

            return [
                'sanction_id' => $sanctionModel->id,
                'has_sanction_active' => false,
                'sanction_expires_at' => $this->faker->dateTimeBetween('-90 days', '-1 day'),
            ];
        });
    }

    /**
     * Indicate that the moderation has a permanent sanction (no expiry).
     */
    public function withPermanentSanction(? UserModerationSanction $sanction = null): static
    {
        return $this->state(function (array $attributes) use ($sanction) {
            $sanctionModel = $sanction ??  UserModerationSanction::factory()->create();

            return [
                'sanction_id' => $sanctionModel->id,
                'has_sanction_active' => true,
                'sanction_expires_at' => null,
            ];
        });
    }

    /**
     * Indicate that the moderation is for a specific member.
     */
    public function forMember(Member $member): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $member->user_id,
        ]);
    }

    /**
     * Indicate that the moderation was reported by a specific member.
     */
    public function reportedByMember(Member $member): static
    {
        return $this->state(fn (array $attributes) => [
            'reporter_user_id' => $member->user_id,
        ]);
    }

    /**
     * Indicate that the moderation is handled by a specific admin.
     */
    public function moderatedByAdmin(Admin $admin): static
    {
        return $this->state(fn (array $attributes) => [
            'moderator_user_id' => $admin->user_id,
        ]);
    }

    /**
     * Indicate that the moderation is for a specific category.
     */
    public function inCategory(UserModerationCategory|int|string $category): static
    {
        return $this->state(function (array $attributes) use ($category) {
            if ($category instanceof UserModerationCategory) {
                return ['category_id' => $category->id];
            }

            if (is_int($category)) {
                return ['category_id' => $category];
            }

            // Assume it's a key, find the category
            $categoryModel = UserModerationCategory::where('key', $category)->first();
            return ['category_id' => $categoryModel?->id ?? UserModerationCategory::factory()->create()->id];
        });
    }

    /**
     * Indicate that the moderation is for a specific feed post.
     */
    public function forFeedPost(FeedPost|int|null $feedPost): static
    {
        return $this->state(fn (array $attributes) => [
            'feed_post_id' => $feedPost instanceof FeedPost ? $feedPost->id : $feedPost,
        ]);
    }

    /**
     * Indicate that the moderation has no associated feed post.
     */
    public function withoutFeedPost(): static
    {
        return $this->state(fn (array $attributes) => [
            'feed_post_id' => null,
        ]);
    }

    /**
     * Create a spam moderation.
     */
    public function spam(): static
    {
        return $this->inCategory('spam');
    }

    /**
     * Create a harassment moderation.
     */
    public function harassment(): static
    {
        return $this->inCategory('harassment');
    }

    /**
     * Create a hate speech moderation.
     */
    public function hateSpeech(): static
    {
        return $this->inCategory('hate_speech');
    }

    /**
     * Create multiple moderations in different states for testing.
     */
    public static function createMixedStates(?int $count = 1): void
    {
        $moderator = Admin::factory()->create();

        // New/Opened
        UserModeration::factory()->count($count)->create();

        // In Review
        UserModeration::factory()->count($count)->inReview($moderator)->create();

        // Resolved
        UserModeration::factory()->count($count)->resolved($moderator)->create();

        // Resolved with sanction
        UserModeration::factory()
            ->count($count)
            ->resolved($moderator)
            ->withActiveSanction()
            ->create();

        // Closed
        UserModeration::factory()->count($count)->closed($moderator)->create();
    }
}
