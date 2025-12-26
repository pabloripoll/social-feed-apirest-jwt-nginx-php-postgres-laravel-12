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
                'key' => 'app',
                'title_single' => 'Application Message',
                'title_double' => 'Application Messages',
                'title_multiple' => 'Application Messages',
                'message_single' => 'You have 1 application message to read.',
                'message_double' => 'You have 2 application messages to read.',
                'message_multiple' => 'You have <count> application messages to read.',
            ],
            [
                'key' => 'moderation',
                'title_single' => 'Moderation Message',
                'title_double' => 'Moderation Messages',
                'title_multiple' => 'Moderation Messages',
                'message_single' => 'You have 1 moderation message to attend.',
                'message_double' => 'You have 2 moderation messages to attend.',
                'message_multiple' => 'You have <count> moderation messages to attend.',
            ],
            [
                'key' => 'following-feed-post',
                'title_single' => 'New Feed Post',
                'title_double' => 'New Feed Posts',
                'title_multiple' => 'New Feed Posts',
                'message_single' => '@<member> has created a new Feed Post.',
                'message_double' => '@<member> and one more member have created a new Feed Post.',
                'message_multiple' => '@<member> and others <count> members have created a new Feed Post.',
            ],
            [
                'key' => 'feed-post-thumb-up',
                'title_single' => 'New Thumb Up on your Feed Post',
                'title_double' => 'New Thumbs Up on your Feed Post',
                'title_multiple' => 'New Thumbs Up on your Feed Post',
                'message_single' => '@<member> has gave you a thumb up on you publication <fee-post-title>.',
                'message_double' => '@<member>> and one more member have gave you a thumb up on you publication <fee-post-title>.',
                'message_multiple' => '@<member> and others <count> members have gave you a thumb up on you publication <feed-post-title>.',
            ],
            [
                'key' => 'new-follower',
                'title_single' => 'New Follower',
                'title_double' => 'New Followers',
                'title_multiple' => 'New Followers',
                'message_single' => '@<member> has started to follow you.',
                'message_double' => '@<member> and one more member have started to follow you.',
                'message_multiple' => '@<member> and others <count> members have started to follow you.',
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
                    'title_single'      => $type['title_single'],
                    'title_double'      => $type['title_double'],
                    'title_multiple'    => $type['title_multiple'],
                    'message_single'    => $type['message_single'],
                    'message_double'    => $type['message_double'],
                    'message_multiple'  => $type['message_multiple'],
                ]
            );
        }
    }
}
