<?php

namespace App\Domain\Member\Database\Seeders;

use App\Domain\Member\Models\MemberNotificationType;
use Illuminate\Database\Seeder;

class MemberNotificationTypeSeeder extends Seeder
{
    protected function types(): array
    {
        return [
            [
                'key' => 'new-feed-post',
                'title' => 'New feed post',
                'message_singular' => 'New Feed Post from @<member>.',
                'message_multiple' => 'New Feed Posts from @<member> and others <count> members.',
            ],
            [
                'key' => 'new-feed-post-thumb-up',
                'title' => 'Thumbs up on feed post',
                'message_singular' => 'New thumb up from @<member> on <fee-post-title>.',
                'message_multiple' => 'New thumbs up from @<member> and others <count> members on <feed-post-title>.',
            ],
        ];
    }

    /**
     * $ php artisan db:seed
     */
    public function run(): void
    {
        foreach ($this->types() as $type) {
            MemberNotificationType::updateOrCreate(
                ['key' => $type['key']],
                [
                    'title' => $type['title'],
                    'message_singular' => $type['message_singular'],
                    'message_multiple' => $type['message_multiple'],
                ]
            );
        }
    }
}
