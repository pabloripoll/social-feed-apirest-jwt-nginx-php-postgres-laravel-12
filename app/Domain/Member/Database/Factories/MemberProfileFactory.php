<?php

namespace App\Domain\Member\Database\Factories;

use App\Domain\Member\Models\MemberProfile;
use App\Domain\User\Models\User;
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
            'name' => $this->faker->regexify('[A-Za-z0-9]{8,64}'),
            'age' => $this->faker->numberBetween(18, 99),
        ];
    }

    /**
     * Configure the factory to automatically create related entities after a member is created.
     *
     * This will create a MemberProfile nickname and a readable name from the user's email.
     */
    public function configure()
    {
        return $this->afterCreating(function (MemberProfile $profile) {
            // ensure relations are up to date
            $profile->refresh();

            if (! $profile->user || ! $profile->user->email) {
                return;
            }

            $email = $profile->user->email;
            // local part before the @
            $local = strstr($email, '@', true) ?: '';

            // nickname: keep previous behaviour (alphanumeric, max 32 chars)
            $profile->nickname = substr(preg_replace('/[^A-Za-z0-9]/', '', $local), 0, 32);

            // Build a human-readable name:
            // split on common separators (dot, underscore, hyphen, plus)
            // support multibyte characters and title-case each part
            $parts = preg_split('/[._\-\+]+/u', $local);
            $parts = array_filter($parts, fn ($p) => $p !== '');

            $parts = array_map(function ($part) {
                // convert to lowercase then title-case (handles multibyte)
                return mb_convert_case($part, MB_CASE_TITLE, 'UTF-8');
            }, $parts);

            // Join with a space: "john-doe" => "John Doe", "alice" => "Alice"
            $name = $parts ? implode(' ', $parts) : null;

            $profile->name = $name;

            // Ensure age is within 18..99 (if not, replace with a random valid age)
            if (! is_int($profile->age) || $profile->age < 18 || $profile->age > 99) {
                $profile->age = random_int(18, 99);
            }

            $profile->save();
        });
    }
}
