<?php

namespace App\Domain\User\Database\Seeders;

use App\Domain\User\Models\UserModerationCategory;
use Illuminate\Database\Seeder;

class UserModerationCategorySeeder extends Seeder
{
    protected function types(): array
    {
        return [
            [
                'key' => 'damaging',
                'title' => 'Private damaging content',
                'description' => 'Confidential information, Negative comments about people, Irrelevant or insensitive content, Irresponsible or insensitive content.',
                'level' => 1,
                'position' => 2,
            ],
            [
                'key' => 'inappropriate',
                'title' => 'Inappropriate content',
                'description' => 'Explicit material, Spam, Misinformation and fake news, Profanity and vulgarity, Bullying and harassment.',
                'level' => 2,
                'position' => 1,
            ],
            [
                'key' => 'harmful',
                'title' => 'Harmful and illegal content',
                'description' => 'Graphic violence, Extremism, Hate speech, Abuse material, Restricted goods, Illegal activities.',
                'level' => 3,
                'position' => 3,
            ],
            [
                'key' => 'bullying',
                'title' => 'Bullying behaviour',
                'description' => 'Violent comments, Hate speech, Suspicious of illegal activities.',
                'level' => 3,
                'position' => 4,
            ],
        ];
    }

    /**
     * $ php artisan db:seed
     */
    public function run(): void
    {
        foreach ($this->types() as $type) {
            UserModerationCategory::updateOrCreate(
                ['key' => $type['key']],
                [
                    'title' => $type['title'],
                    'description' => $type['description'],
                    'level' => $type['level'],
                    'position' => $type['position'],
                ]
            );
        }
    }
}
