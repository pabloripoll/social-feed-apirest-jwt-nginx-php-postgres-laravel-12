<?php

namespace App\Domain\Member\Controller;

use App\Domain\User\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Support\Paginate;
use App\Domain\Member\Resources\MemberNotificationResource;
use Carbon\Carbon;

class MemberNotificationController
{
    /**
     * GET /api/v1/account/notifications
     */
    public function listing(Request $request): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();

        $query = UserNotification::query()
            ->with(['type'])
            ->where('receiver_id', $user->id);

        $listing = Paginate::listing($query->count());

        $notifications = $query->paginate($listing->limit, ['*'], 'page', $listing->page);

        $response = [
            'listing' => $listing,
            'result' => MemberNotificationResource::collection($notifications),
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/account/notifications/{uid}
     */
    public function read(int $notification_uid): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();

        $notification = UserNotification::query()
            ->with(['type'])
            ->where('uid', $notification_uid)
            ->where('receiver_id', $user->id)
            ->first();

        if (! $notification) {
            return response()->json([
                    'message' => 'Notification not found.',
                    'error' => 'notification_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        return response()->json(new MemberNotificationResource($notification), JsonResponse::HTTP_OK);
    }

    /**
     * POST /api/v1/account/notifications/{uid}
     */
    public function markAsOpened(int $notification_uid): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();

        $notification = UserNotification::query()
            ->with(['type'])
            ->where('uid', $notification_uid)
            ->where('receiver_id', $user->id)
            ->first();

        if (! $notification) {
            return response()->json([
                    'message' => 'Notification not found.',
                    'error' => 'notification_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        if ($notification->opened === true) {
            return response()->json([
                    'message' => 'Notification already marked as opened.',
                    'error' => 'notification_already_opened',
                ],
                JsonResponse::HTTP_NOT_ACCEPTABLE
            );
        }

        $notification->opened = true;
        $notification->opened_at = Carbon::now();
        $notification->save();

        $response = [
            'message' => 'Notification set as opened.',
            'notification' => new MemberNotificationResource($notification),
        ];

        return response()->json($response, JsonResponse::HTTP_ACCEPTED);
    }

    /**
     * GET /api/v1/account/notifications/{uid}
     */
    public function delete(int $notification_uid): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();

        $notification = UserNotification::query()
            ->with(['type'])
            ->where('uid', $notification_uid)
            ->where('receiver_id', $user->id)
            ->first();

        if (! $notification) {
            return response()->json([
                    'message' => 'Notification not found.',
                    'error' => 'notification_not_found',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $legacyNotification = new MemberNotificationResource($notification);

        $notification->delete();

        $response = [
            'message' => 'Notification has been deleted.',
            'deleted_notification' => $legacyNotification,
        ];

        return response()->json($response, JsonResponse::HTTP_ACCEPTED);
    }
}
