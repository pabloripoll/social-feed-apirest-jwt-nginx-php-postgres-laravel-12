<?php

namespace App\Modules\Feed\Database\Factories;

use App\Modules\Feed\Models\FeedMedia;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Feed\Models\FeedMedia>
 */
class FeedMediaFactory extends Factory
{
    /**
     * The correspond model used by the factory.
     */
    protected $model = FeedMedia::class;

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
