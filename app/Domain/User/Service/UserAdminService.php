<?php

namespace App\Domain\User\Service;

class UserAdminService
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
