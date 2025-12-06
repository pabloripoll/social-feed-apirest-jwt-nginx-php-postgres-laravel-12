<?php

namespace App\Domain\Feed\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Feed\Models\FeedCategory;

class FeedCategorySeeder extends Seeder
{
    protected function categories(): array
    {
        return [
            [
                'key' => 'informatics',
                'title' => 'Informatics',
            ],
            [
                'key' => 'electronics',
                'title' => 'Electronics',
            ],
            [
                'key' => 'electricity',
                'title' => 'Electricity',
            ],
            [
                'key' => 'sound-audio',
                'title' => 'Sound and Audio',
            ],
            [
                'key' => 'mechanics',
                'title' => 'Mechanics',
            ],
            [
                'key' => 'chemistry',
                'title' => 'Chemistry',
            ],
            [
                'key' => 'mathematics',
                'title' => 'Mathematics',
            ],
        ];
    }

    /**
     * $ php artisan db:seed
     */
    public function run(): void
    {
        foreach ($this->categories() as $type) {
            FeedCategory::updateOrCreate(
                ['key' => $type['key']],
                [
                    'title' => $type['title'],
                ]
            );
        }
    }
}
