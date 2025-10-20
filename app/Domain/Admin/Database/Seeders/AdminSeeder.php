<?php

namespace App\Domain\Admin\Database\Seeders;

use App\Domain\Admin\Models\Admin;
use App\Domain\Admin\Models\AdminProfile;
use App\Domain\Geo\Models\GeoRegion;
use App\Domain\User\Models\Role;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * $ php artisan db:seed
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'role' => Role::ADMIN,
                'password' => Hash::make('12345678aZ!'),
            ]
        );

        $regionId = GeoRegion::where('name', 'Western')
            ->whereHas('continent', function ($query) {
                $query->where('name', 'Europe');
            })
            ->value('id');

        Admin::updateOrCreate(
            ['user_id' => $user->id],
            [
                'region_id' => $regionId,
            ]);

        AdminProfile::updateOrCreate(
            ['nickname' => 'admin'],
            [
                'user_id' => $user->id,
            ]);
    }
}
