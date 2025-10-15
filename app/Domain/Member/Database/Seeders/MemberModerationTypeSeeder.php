<?php

namespace App\Domain\Member\Database\Seeders;

use App\Domain\Member\Models\MemberModerationType;
use Illuminate\Database\Seeder;

class MemberModerationTypeSeeder extends Seeder
{
    protected function types(): array
    {
        return [
            [
                'key' => 'user-banned',
                'title' => 'User Banned',
                'description' => 'User access and its content forbidden.',
            ],
            [
                'key' => 'user-suspension',
                'title' => 'User Suspension',
                'description' => 'User access suspension for a determined period of time.',
            ],
            [
                'key' => 'post-banned',
                'title' => 'Post Banned',
                'description' => 'Post access forbidden.',
            ],
            [
                'key' => 'post-suspension',
                'title' => 'Post Suspension',
                'description' => 'Post suspended due to non-compliance with community standards and/or quality protocol.',
            ],
        ];
    }

    /**
     * $ php artisan db:seed
     * $ php artisan db:seed app\\Domain\\Member\\Database\\Seeders\\MemberModerationTypeSeeder.php
     */
    public function run(): void
    {
        $pos = 0;
        foreach ($this->types() as $type) {
            $pos++;
            MemberModerationType::updateOrCreate(
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
