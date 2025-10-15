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
                'key' => 'new-following-post',
                'title' => 'New following post',
                'message_singular' => 'New post from @<member>.',
                'message_multiple' => 'New posts from @<member> and others <count> members.',
            ],
            [
                'key' => 'new-post-vote',
                'title' => 'New post vote',
                'message_singular' => 'New vote from @<member> on <post-title>.',
                'message_multiple' => 'New votes from @<member> and others <count> members on <post-title>.',
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
