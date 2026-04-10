<?php

namespace App\Modules\Admin\Database\Seeders;

use App\Modules\Admin\Models\Admin;
use App\Modules\Admin\Models\AdminProfile;
use App\Modules\Geo\Models\GeoRegion;
use App\Modules\User\Models\Role;
use App\Modules\User\Models\User;
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

        $region = GeoRegion::where('name', 'Western')
            ->whereHas('continent', function ($query) {
                $query->where('name', 'Europe');
            })
            ->first();

        Admin::updateOrCreate(
            ['user_id' => $user->id],
            [
                'continent_id' => $region->continent_id,
                'region_id' => $region->id,
            ]);

        AdminProfile::updateOrCreate(
            ['nickname' => 'admin'],
            [
                'user_id' => $user->id,
            ]);
    }
}
