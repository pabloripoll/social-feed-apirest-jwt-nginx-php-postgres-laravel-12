<?php

namespace App\Domain\Member\Service;

class MemberNotificationService
{
    public function notifyNewFollower(object $payload): object
    {
        $response = (object) ['test' => true];

        return $response;
    }

    public function notifyNewPost(object $payload): object
    {
        $response = (object) ['test' => true];

        return $response;
    }

    public function notifyNewVote(object $payload): object
    {
        $response = (object) ['test' => true];

        return $response;
    }
}
