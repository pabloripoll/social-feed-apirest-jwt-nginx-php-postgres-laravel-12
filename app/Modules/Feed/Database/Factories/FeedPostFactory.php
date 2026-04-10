<?php

namespace App\Modules\Feed\Database\Factories;

use App\Modules\Feed\Models\FeedCategory;
use App\Modules\Feed\Models\FeedPost;
use App\Modules\Geo\Models\GeoRegion;
use App\Modules\Member\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Feed\Models\FeedPost>
 */
class FeedPostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FeedPost::class;

    /**
     * Define the model's default state.
     *
     * Behavior:
     * - If 'user_id' is provided in attributes, it will be used.
     * - If 'user' is provided (Member or User), the factory will extract a user id.
     * - Otherwise a Member is created and its user_id will be used.
     * - A random existing FeedCategory will be chosen when available; otherwise a category is created.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $attrs = $this->attributes ?? [];

        $userId = $attrs['user_id'] ?? null;

        if ($userId === null && array_key_exists('user', $attrs)) {
            $user = $attrs['user'];
            if ($user instanceof Member) {
                $userId = $user->user_id;
            } elseif (is_object($user) && property_exists($user, 'id')) {
                $userId = $user->id;
            }
        }

        if (empty($userId)) {
            $member = Member::factory()->create();
            $userId = $member->user_id;
        }

        $category = FeedCategory::inRandomOrder()->first();
        if (! $category) {
            $category = FeedCategory::factory()->create();
        }

        $region = GeoRegion::inRandomOrder()->first();

        $title = $this->faker->sentence(6);
        $article = $this->faker->paragraphs(5, true);

        // uid is created by model
        return [
            'user_id' => $userId,
            'category_id' => $category->id,
            'continent_id' => $region->continent_id,
            'region_id' => $region->id,
            'is_sketch' => false,
            'is_draft' => false,
            'is_active' => true,
            'is_banned' => false,
            'title' => $title,
            'slug' => Str::limit(Str::slug($title), 128),
            'summary' => Str::limit(trim(strip_tags($article)), 128),
            'article' => $article,
        ];
    }

    /**
     * Configure the factory to update related counters after creating a post.
     *
     * This ensures the Member.feed_posts_count (and category.posts_count) are incremented
     * consistently when creating posts via the factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (FeedPost $post) {
            // Increment member counter (guard against null user_id)
            if (! empty($post->user_id)) {
                // Member::where('user_id', ...) because Member->user_id references users table id
                Member::where('user_id', $post->user_id)->increment('feed_posts_count');
            }

            // Increment category counter (guard against null category_id)
            if (! empty($post->category_id)) {
                FeedCategory::where('id', $post->category_id)->increment('posts_count');
            }
        });
    }
}
