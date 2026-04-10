<?php

namespace App\Modules\User\Service;

class UserMemberService
{
    /**
     * Listing Filters
     */
    public static function filters(): array
    {
        return [
            'sorting' => [
                'recent' => 'Recent',
                'oldest' => 'Oldest',
            ],
        ];
    }
}
