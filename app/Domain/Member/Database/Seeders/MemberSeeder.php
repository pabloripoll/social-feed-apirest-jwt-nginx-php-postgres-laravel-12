<?php

namespace App\Domain\Member\Database\Seeders;

use App\Domain\Member\Models\Member;
use App\Domain\Member\Models\MemberActivationCode;
use App\Domain\Member\Models\MemberProfile;
use App\Models\GeoRegion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    /**
     * $ php artisan db:seed
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'member@example.com'],
            [
                'role' => Role::MEMBER,
                'password' => Hash::make('12345678aZ!'),
            ]
        );

        $regionId = GeoRegion::where('name', 'Western')
            ->whereHas('continent', function ($query) {
                $query->where('name', 'Europe');
            })
            ->value('id');

        Member::updateOrCreate(
            ['user_id' => $user->id],
            [
                'region_id' => $regionId,
            ]
        );

        MemberActivationCode::updateOrCreate(
            ['user_id' => $user->id],
            [
                'is_active' => true,
            ]
        );

        MemberProfile::updateOrCreate(
            ['nickname' => 'member'],
            [
                'user_id' => $user->id,
            ]
        );
    }
}
