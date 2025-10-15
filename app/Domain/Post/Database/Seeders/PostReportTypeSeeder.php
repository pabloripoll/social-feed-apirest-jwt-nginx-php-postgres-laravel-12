<?php

namespace App\Domain\Post\Database\Seeders;

use App\Domain\Post\Models\PostReportType;
use Illuminate\Database\Seeder;

class PostReportTypeSeeder extends Seeder
{
    protected function types(): array
    {
        return [
            [
                'key' => 'damaging',
                'title' => 'Private damaging content',
                'description' => 'Confidential information, Negative comments about people, Irrelevant or insensitive content, Irresponsible or insensitive content...',
                'level' => 1,
                'position' => 2,
            ],
            [
                'key' => 'inappropriate',
                'title' => 'Inappropriate content',
                'description' => 'Explicit material, Spam, Misinformation and fake news, Profanity and vulgarity, Bullying and harassment...',
                'level' => 2,
                'position' => 1,
            ],
            [
                'key' => 'harmful',
                'title' => 'Harmful and illegal content',
                'description' => 'Graphic violence, Extremism, Hate speech, Abuse material, Restricted goods, Illegal activities...',
                'level' => 3,
                'position' => 3,
            ],
        ];
    }

    /**
     * $ php artisan db:seed
     */
    public function run(): void
    {
        foreach ($this->types() as $type) {
            PostReportType::updateOrCreate(
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
