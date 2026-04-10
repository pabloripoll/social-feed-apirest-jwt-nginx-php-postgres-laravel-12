<?php

namespace Database\Seeders;

use App\Modules\User\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        /* User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]); */

        $this->call([
            \App\Modules\Geo\Database\Seeders\GeoSeeder::class,
            \App\Modules\User\Database\Seeders\UserRoleSeeder::class,
            \App\Modules\Admin\Database\Seeders\AdminSeeder::class,
            \App\Modules\Member\Database\Seeders\MemberSeeder::class,
            \App\Modules\Feed\Database\Seeders\FeedCategorySeeder::class,
            \App\Modules\User\Database\Seeders\UserNotificationTypeSeeder::class,
            \App\Modules\User\Database\Seeders\UserModerationCategorySeeder::class,
            \App\Modules\User\Database\Seeders\UserModerationSanctionSeeder::class,
        ]);
    }
}
