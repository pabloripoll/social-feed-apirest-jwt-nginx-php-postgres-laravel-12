<?php

namespace App\Domain\User\Database\Factories;

use App\Domain\User\Models\UserActivationCode;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\User\Models\UserActivationCode>
 */
class UserActivationCodeFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected $model = UserActivationCode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numberBetween(100000, 999999),
            'user_id' => User::factory(), // or null if you want sometimes unassigned
            'is_active' => true,
        ];
    }

    /**
     * Set the is_active state for the factory.
     *
     * Use this method to specify whether the created entity should be active or inactive.
     *
     * @param  bool|null  $state  Whether the entity should be active (true) or inactive (false). Defaults to true.
     */
    public function isActive(?bool $state = true): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => $state,
        ]);
    }
}
