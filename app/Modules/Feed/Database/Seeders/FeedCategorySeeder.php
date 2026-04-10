<?php

namespace App\Modules\Feed\Database\Seeders;

use App\Modules\Feed\Models\FeedCategory;
use Illuminate\Database\Seeder;

class FeedCategorySeeder extends Seeder
{
    protected function categories(): array
    {
        return [
            [
                'key' => 'example',
                'title' => 'Example',
                'position' => 1,
            ],
            [
                'key' => 'thoughts',
                'title' => 'Thoughts',
                'position' => 2,
            ],
            [
                'key' => 'cooking',
                'title' => 'Cooking',
                'position' => 3,
            ],
            [
                'key' => 'hands-on',
                'title' => 'Hands On',
                'position' => 4,
            ],
            [
                'key' => 'decoration',
                'title' => 'Decoration',
                'position' => 5,
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
                    'position' => $type['position'],
                    'title' => $type['title'],
                ]
            );
        }
    }
}
