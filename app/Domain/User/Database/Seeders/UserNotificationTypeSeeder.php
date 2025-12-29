<?php

namespace App\Domain\User\Database\Seeders;

use App\Domain\User\Models\UserNotificationType;
use Illuminate\Database\Seeder;

class UserNotificationTypeSeeder extends Seeder
{
    protected function types(): array
    {
        return [
            [
                'key' => 'account',
                'title_single' => 'Account Message',
                'title_multiple' => 'Account Messages',
                'summary_single' => 'You have 1 account message to read.',
                'summary_multiple' => 'You have <count> account messages to read.',
            ],
            [
                'key' => 'moderation',
                'title_single' => 'Moderation Message',
                'title_multiple' => 'Moderation Messages',
                'summary_single' => 'You have 1 moderation message to attend.',
                'summary_multiple' => 'You have <count> moderation messages to attend.',
            ],
            [
                'key' => 'new-feed-post',
                'title_single' => 'New Feed Post',
                'title_multiple' => 'New Feed Posts',
                'summary_single' => '@<nickname> has created a new Feed Post.',
                'summary_multiple' => '@<nickname> and <count> more members have created a new Feed Post.',
            ],
            [
                'key' => 'new-feed-post-thumb-up',
                'title_single' => 'New Thumb-Up on your Feed Post',
                'title_multiple' => 'New Thumbs-Ups on your Feed Post',
                'summary_single' => '@<nickname> has gave you a thumb up on your publication <feed-post-title>.',
                'summary_multiple' => '@<nickname> and <count> more members have gave you a thumb up on your publication <feed-post-title>.',
            ],
            [
                'key' => 'new-follower',
                'title_single' => 'New Follower',
                'title_multiple' => 'New Followers',
                'summary_single' => '@<nickname> has started to follow you.',
                'summary_multiple' => '@<nickname> and <count> more members have started to follow you.',
            ],
        ];
    }

    /**
     * $ php artisan db:seed
     */
    public function run(): void
    {
        foreach ($this->types() as $type) {
            UserNotificationType::updateOrCreate(
                ['key' => $type['key']],
                [
                    'title_single'      => $type['title_single'],
                    'title_multiple'    => $type['title_multiple'],
                    'summary_single'    => $type['summary_single'],
                    'summary_multiple'  => $type['summary_multiple'],
                ]
            );
        }
    }
}
