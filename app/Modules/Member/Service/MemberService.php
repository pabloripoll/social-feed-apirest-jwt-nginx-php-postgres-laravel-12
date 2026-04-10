<?php

namespace App\Modules\Member\Service;

use App\Modules\User\Models\User;

class MemberService
{
    /**
     * Checks user role member access state
     * $user->member must be loaded in controller
     */
    public function checkAccess(User $user): object
    {
        $response = new \stdClass;
        $response->status = true;

        if (! $user->member->is_active) {
            $response->message = 'Member is banned and cannot create any feed post.';
            $response->error = 'user_not_activated';
            $response->status = false;
        }

        if ($user->member->is_banned) {
            $response->message = 'Member is banned and cannot create any feed post.';
            $response->error = 'user_banned';
            $response->status = false;
        }

        return $response;
    }
}
