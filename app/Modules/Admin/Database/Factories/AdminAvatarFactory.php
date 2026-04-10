<?php

namespace App\Modules\Admin\Database\Factories;

use App\Modules\Admin\Models\AdminAvatar;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Admin\Models\AdminAvatar>
 */
class AdminAvatarFactory extends Factory
{
    /**
     * The correspond model used by the factory.
     */
    protected $model = AdminAvatar::class;

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
            'is_expired' => false,
            'expires_at' => now()->addMinutes($this->jwtTime),
            'refresh_count' => 0,
            'ip_address' => $this->faker->ipv4,
            'user_agent' => $this->faker->userAgent,
            'payload' => [],
            'requests_count' => 1,
            'token' => Str::random(64),
        ];
    }
}
