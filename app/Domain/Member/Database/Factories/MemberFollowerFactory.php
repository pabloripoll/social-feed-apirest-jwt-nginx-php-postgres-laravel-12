<?php

namespace App\Domain\Member\Database\Factories;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for members_followings pivot table.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class MemberFollowerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * Since this is a pivot table without a dedicated model,
     * you may want to create a MemberFollower model or leave this null.
     *
     * @var string|null
     */
    protected $model = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'following_user_id' => User::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Set a specific user being followed.
     *
     * @param  int|User  $user
     * @return static
     */
    public function user(int|User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user instanceof User ? $user->id : $user,
        ]);
    }

    /**
     * Set a specific following.
     *
     * @param  int|User  $following
     * @return static
     */
    public function following(int|User $following): static
    {
        return $this->state(fn (array $attributes) => [
            'following_user_id' => $following instanceof User ? $following->id : $following,
        ]);
    }
}
