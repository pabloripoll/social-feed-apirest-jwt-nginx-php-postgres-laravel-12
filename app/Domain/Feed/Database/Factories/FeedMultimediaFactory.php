<?php

namespace App\Domain\Feed\Database\Factories;

use App\Domain\Feed\Models\FeedMultimedia;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Feed\Models\FeedMultimedia>
 */
class FeedMultimediaFactory extends Factory
{
    /**
     * The correspond model used by the factory.
     */
    protected $model = FeedMultimedia::class;

    /**
     * JWT access expiration, smaller than JWT TTL config
     */
    protected $jwtTime = 60;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
        ];
    }
}
