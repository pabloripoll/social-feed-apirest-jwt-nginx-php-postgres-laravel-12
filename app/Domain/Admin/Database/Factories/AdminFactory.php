<?php

namespace App\Domain\Admin\Database\Factories;

use App\Domain\Admin\Models\Admin;
use App\Domain\Admin\Models\AdminAccessLog;
use App\Domain\Admin\Models\AdminProfile;
use App\Domain\Geo\Models\GeoRegion;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Admin\Models\Admin>
 */
class AdminFactory extends Factory
{
    /**
     * The correspond model used by the factory.
     */
    protected $model = Admin::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $region = GeoRegion::query()->inRandomOrder()->first();

        return [
            'uid' => $this->faker->unique()->numberBetween(100000, 999999),
            'user_id' => User::factory()->state(['role' => Role::ADMIN]),
            'continent_id' => $region->continent_id,
            'region_id' => $region->id,
            'is_active' => true,
            'is_banned' => false,
        ];
    }

    /**
     * Configure the factory to automatically create related entities after a member is created.
     *
     * This will create a AdminProfile and an active AdminActivationCode for the newly created member.
     */
    public function configure()
    {
        return $this->afterCreating(function (Admin $member) {
            AdminProfile::factory()
                ->create([
                    'user_id' => $member->user_id,
                    'nickname' => preg_replace('/[^A-Za-z0-9]/', '', strstr($member->user->email, '@', true)),
                ]);
        });
    }

    /**
     * State to create a AdminAccessLog with a real JWT token for the member after creation.
     *
     * Optionally, additional access log attributes can be provided via the $accessLogAttributes array.
     *
     * @param  array  $accessLogAttributes  Additional attributes for AdminAccessLog
     */
    public function withAuth(array $accessLogAttributes = []): static
    {
        return $this->afterCreating(function ($member) use ($accessLogAttributes) {
            $jwt = JWTAuth::fromUser($member->user);

            AdminAccessLog::factory()
                ->create(array_merge([
                    'user_id' => $member->user_id,
                    'token' => $jwt,
                ], $accessLogAttributes));
        });
    }
}
