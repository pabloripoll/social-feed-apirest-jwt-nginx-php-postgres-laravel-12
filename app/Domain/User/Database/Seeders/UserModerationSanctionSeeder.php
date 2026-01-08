<?php

namespace App\Domain\User\Database\Seeders;

use App\Domain\User\Models\UserModerationSanction;
use Illuminate\Database\Seeder;

class UserModerationSanctionSeeder extends Seeder
{
    protected function types(): array
    {
        return [
            [
                'key' => 'none',
                'title' => 'No Sanction',
                'description' => 'No sanction is applied on moderation.',
            ],
            [
                'key' => 'user-banned',
                'title' => 'User Banned',
                'description' => 'User access and its content forbidden.',
            ],
            [
                'key' => 'user-suspended',
                'title' => 'User Suspended',
                'description' => 'User access suspended for a determined period of time.',
            ],
            [
                'key' => 'user-feed-restriction',
                'title' => 'User Feed Restriction',
                'description' => 'User feed actions restricted for a determined period of time.',
            ],
            [
                'key' => 'feed-post-banned',
                'title' => 'Feed Post Banned',
                'description' => 'Feed post access forbidden due to non-compliance with community standards and/or quality protocol.',
            ],
            [
                'key' => 'feed-post-suspended',
                'title' => 'Feed Post Suspended',
                'description' => 'Feed post access suspended but can be available again after the suggested adjustments to comply community standards and/or quality protocol.',
            ],
        ];
    }

    /**
     * $ php artisan db:seed
     */
    public function run(): void
    {
        $pos = 0;
        foreach ($this->types() as $type) {
            $pos++;
            UserModerationSanction::updateOrCreate(
                ['key' => $type['key']],
                [
                    'title' => $type['title'],
                    'description' => $type['description'],
                    'position' => $pos,
                ]
            );
        }
    }
}
