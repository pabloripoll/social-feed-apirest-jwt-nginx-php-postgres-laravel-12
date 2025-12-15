<?php

namespace App\Domain\Member\Database\Seeders;

use App\Domain\Geo\Models\GeoRegion;
use App\Domain\Member\Models\Member;
use App\Domain\Member\Models\MemberProfile;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use App\Domain\User\Models\UserActivationCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    /**
     * Base members to test api manually
     */
    protected function members(): array
    {
        return [
            [
                'email' => 'member@example.com',
                'nickname' => 'member',
            ],
            [
                'email' => 'tester@example.com',
                'nickname' => 'tester',
            ],
        ];
    }

    /**
     * $ php artisan db:seed
     */
    public function run(): void
    {
        foreach ($this->members() as $member) {
            $user = User::firstOrCreate(
                ['email' => $member['email']],
                [
                    'role' => Role::MEMBER,
                    'password' => Hash::make('12345678aZ!'),
                ]
            );

            $region = GeoRegion::where('name', 'Western')
                ->whereHas('continent', function ($query) {
                    $query->where('name', 'Europe');
                })
                ->first();

            Member::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'continent_id' => $region->continent_id,
                    'region_id' => $region->id,
                ]
            );

            UserActivationCode::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'is_active' => true,
                ]
            );

            MemberProfile::updateOrCreate(
                ['nickname' => $member['nickname']],
                [
                    'user_id' => $user->id,
                ]
            );
        }
    }
}
