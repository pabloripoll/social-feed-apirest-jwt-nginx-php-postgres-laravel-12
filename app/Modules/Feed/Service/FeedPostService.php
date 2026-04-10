<?php

namespace App\Modules\Feed\Service;

use App\Modules\Feed\Models\FeedCategory;

class FeedPostService
{
    /**
     * Feed Post Filters
     */
    public static function filters(): array
    {
        $categories = [];
        $feedCategories = FeedCategory::select(['key', 'title'])
            ->orderBy('position', 'asc')
            ->get();

        foreach ($feedCategories as $row) {
            $categories[$row['key']] = $row['title'];
        }

        return [
            'categories' => $categories,
            'sorting' => [
                'recent' => 'Recent',
                'oldest' => 'Oldest',
                'thumbs-up' => 'Thumbs Up',
                'thumbs-down' => 'Thumbs Down',
            ],
        ];
    }
}
