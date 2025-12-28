<?php

namespace App\Domain\User\Dto;

class UserNotificationDto
{
    public function __construct(
        public ?int $performerId = null,
        public ?array $performerData = null,
        public ?int $receiverId = null,
        public ?array $receiverData = null,
    ) {}
}
