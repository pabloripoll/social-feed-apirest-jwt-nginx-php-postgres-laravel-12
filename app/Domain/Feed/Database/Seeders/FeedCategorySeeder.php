<?php

namespace App\Domain\Feed\Database\Seeders;

use App\Domain\Feed\Models\FeedCategory;
use Illuminate\Database\Seeder;

class FeedCategorySeeder extends Seeder
{
    protected function categories(): array
    {
        return [
            [
                'key' => 'thoughts',
                'title' => 'Thoughts',
            ],
            [
                'key' => 'cooking',
                'title' => 'Cooking',
            ],
            [
                'key' => 'hands-on',
                'title' => 'Hands On',
            ],
            [
                'key' => 'decoration',
                'title' => 'Decoration',
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
