<?php

namespace App\Domain\User\Database\Factories;

use App\Domain\User\Models\UserModerationSanction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\User\Models\UserModerationSanction>
 */
class UserModerationSanctionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserModerationSanction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->words(2, true); // e.g. "Spam Policy"
        $key = Str::slug($title) ?: $this->faker->unique()->word();

        return [
            'key' => $key,
            'position' => $this->faker->numberBetween(0, 100),
            'title' => ucfirst($title),
            'description' => $this->faker->sentence(),
        ];
    }
}
