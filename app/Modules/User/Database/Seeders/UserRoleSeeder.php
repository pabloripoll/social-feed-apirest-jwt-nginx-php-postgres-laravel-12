<?php

namespace App\Modules\User\Database\Seeders;

use App\Modules\User\Models\Role;
use App\Modules\User\Models\UserRole;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * $ php artisan db:seed --class=RoleSeeder
     */
    public function run(): void
    {
        UserRole::firstOrCreate(
            ['key' => Role::ADMIN],
            [
                'title' => 'Administrator',
                'description' => 'User that administrates the application.',
            ]
        );

        UserRole::firstOrCreate(
            ['key' => Role::MEMBER],
            [
                'title' => 'Member',
                'description' => 'User that participates as reader and writer of the social contents.',
            ]
        );
    }
}
