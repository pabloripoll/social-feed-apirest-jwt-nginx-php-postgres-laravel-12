<?php

namespace App\Domain\User\Service;

use App\Domain\Member\Models\MemberFollower;
use App\Domain\User\Models\UserNotification;
use App\Domain\User\Models\UserNotificationType;
use App\Domain\User\Dto\UserNotificationDto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class UserNotificationService
{
    /**
     * Handlers
     */

    public function newFollower(UserNotificationDto $dto): void
    {
        $this->sendNotification('new-follower', $dto);
    }

    public function newFeedPost(UserNotificationDto $dto): void
    {
        $this->sendNotification('new-feed-post', $dto);
    }

    public function newFeedPostThumbUp(UserNotificationDto $dto): void
    {
        $this->sendNotification('new-feed-post-thumb-up', $dto);
    }

    /**
     * Executioners
     */

    protected function sendNotification(string $typeKey, UserNotificationDto $dto): void
    {
        $type = UserNotificationType::where('key', $typeKey)->first();

        if (! $type) {
            Log::warning("Notification type not found: {$typeKey}");
            return;
        }

        $receivers = $this->getReceivers($dto);

        foreach ($receivers as $receiverId) {
            $this->createOrUpdateNotification($type, $dto, $receiverId);
        }
    }

    protected function getReceivers(UserNotificationDto $dto): Collection
    {
        if (is_numeric($dto->receiverId)) {
            return collect([$dto->receiverId]);
        }

        return MemberFollower::query()
            ->where('following_user_id', $dto->performerId)
            ->pluck('user_id');
    }

    protected function createOrUpdateNotification(
        UserNotificationType $type,
        UserNotificationDto $dto,
        int $receiverId
    ): void {
        $notification = UserNotification::query()
            ->where('type_id', $type->id)
            ->where('receiver_id', $receiverId)
            ->where('performer_id', $dto->performerId)
            ->where('opened', false)
            ->first();

        $notifyCount = $notification ? ($notification->notify_count + 1) : 1;

        $title = $notifyCount === 1 ? $type->title_single : $type->title_multiple;
        $summary = $notifyCount === 1 ?  $type->summary_single : $type->summary_multiple;

        $summary = str_replace(
            ['<nickname>', '<count>'],
            [$dto->performerData['nickname'], $notifyCount],
            $summary
        );

        $payload = [
            'title' => $title,
            'summary' => $summary,
            'performer' => $dto->performerData,
        ];

        if (! $notification) {
            $notification = new UserNotification;
        }

        $notification->type_id        = $type->id;
        $notification->receiver_id    = $receiverId;
        $notification->performer_id   = $dto->performerId;
        $notification->notify_count   = $notifyCount;
        $notification->opened         = false;
        $notification->opened_at      = null;
        $notification->payload        = json_encode($payload);
        $notification->save();
    }
}
