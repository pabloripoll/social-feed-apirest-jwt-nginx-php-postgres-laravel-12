<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\UserRole;

class RoleSeeder extends Seeder
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
