<?php

namespace App\Domain\Feed\Database\Factories;

use App\Domain\Feed\Models\FeedCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Feed\Models\FeedCategory>
 */
class FeedCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FeedCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->words(2, true); // e.g. "World News"
        $key = Str::slug($title) ?: $this->faker->unique()->word();

        return [
            'key' => $key,
            'position' => $this->faker->numberBetween(0, 100),
            'posts_count' => 0,
            'posts_thumbs_up_count' => 0,
            'posts_thumbs_down_count' => 0,
            'title' => ucfirst($title),
            'description' => $this->faker->sentence(),
        ];
    }
}
