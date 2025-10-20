<?php

namespace App\Domain\Member\Database\Factories;

use App\Domain\User\Models\User;
use App\Domain\Geo\Models\GeoRegion;
use App\Domain\Member\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Domain\Member\Models\MemberProfile;
use App\Domain\Member\Models\MemberAccessLog;
use App\Domain\Member\Models\MemberActivationCode;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Member\Models\Member>
 */
class MemberFactory extends Factory
{
    /**
     * The correspond model used by the factory.
     */
    protected $model = Member::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uid' => $this->faker->unique()->numberBetween(100000, 999999),
            'user_id' => User::factory(),
            'region_id' => GeoRegion::query()->inRandomOrder()->value('id'),
            'is_active' => true,
            'is_banned' => false,
        ];
    }

    /**
     * Configure the factory to automatically create related entities after a member is created.
     *
     * This will create a MemberProfile and an active MemberActivationCode for the newly created member.
     */
    public function configure()
    {
        return $this->afterCreating(function (Member $member) {
            MemberProfile::factory()
                ->create([
                    'user_id' => $member->user_id,
                    'nickname' => preg_replace('/[^A-Za-z0-9]/', '', strstr($member->user->email, '@', true)),
                ]);
            MemberActivationCode::factory()
                ->isActive(true)
                ->create([
                    'user_id' => $member->user_id,
                ]);
        });
    }

    /**
     * State to create a MemberAccessLog with a real JWT token for the member after creation.
     *
     * Optionally, additional access log attributes can be provided via the $accessLogAttributes array.
     *
     * @param array $accessLogAttributes Additional attributes for MemberAccessLog
     * @return static
     */
    public function withAuth(array $accessLogAttributes = []): static
    {
        return $this->afterCreating(function ($member) use ($accessLogAttributes) {
            $jwt = JWTAuth::fromUser($member->user);

            MemberAccessLog::factory()
                ->create(array_merge([
                    'user_id' => $member->user_id,
                    'token' => $jwt,
                ], $accessLogAttributes));
        });
    }
}
