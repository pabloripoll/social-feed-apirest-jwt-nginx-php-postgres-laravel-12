<?php

namespace App\Domain\User\Service;

use App\Domain\User\Models\UserModerationCategory;

class UserModerationService
{
    /**
     * Moderation Filters
     */
    public static function filters(): array
    {
        $categories = UserModerationCategory::select(['key','title'])
            ->orderBy('position', 'asc')
            ->get()
            ->pluck('title', 'key')
            ->toArray();

        $sanctions = UserModerationCategory::select(['key','title'])
            ->orderBy('position', 'asc')
            ->get()
            ->pluck('title', 'key')
            ->toArray();

        return [
            'categories' => $categories,
            'status' => [
                'reviewing' => 'Reviewing',
                'resolved' => 'Resolved',
                'closed' => 'Closed',
            ],
            'sanctions' => $sanctions,
            'sorting' => [
                'recent' => 'Recent',
                'oldest' => 'Oldest',
            ],
            'moderator' => [
                'me' => 'Moderated by me',
                'all' => 'All moderators',
            ],
        ];
    }
}
