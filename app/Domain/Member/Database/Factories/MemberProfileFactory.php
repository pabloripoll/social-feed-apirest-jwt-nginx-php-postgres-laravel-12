<?php

namespace App\Domain\Member\Database\Factories;

use App\Models\User;
use App\Domain\Member\Models\MemberProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Member\Models\MemberProfile>
 */
class MemberProfileFactory extends Factory
{
    protected $model = MemberProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nickname' => $this->faker->regexify('[A-Za-z0-9]{8,32}'),
            'avatar' => null,
        ];
    }

    /**
     * Configure the factory to automatically create related entities after a member is created.
     *
     * This will create a MemberProfile nickname for the newly created member.
     */
    public function configure()
    {
        return $this->afterCreating(function (MemberProfile $profile) {
            $profile->refresh(); // ensure the user relation is loaded
            if ($profile->user) {
                $profile->nickname = substr(preg_replace('/[^A-Za-z0-9]/', '', strstr($profile->user->email, '@', true)), 0, 32);
                $profile->save();
            }
        });
    }
}
