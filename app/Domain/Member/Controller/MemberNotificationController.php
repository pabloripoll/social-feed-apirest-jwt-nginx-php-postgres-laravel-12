<?php

namespace App\Domain\Member\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Support\Paginate;
use Illuminate\Support\Facades\Validator;

class MemberNotificationController
{
    /**
     * GET /api/v1/account/notifications
     */
    public function listing(Request $request): JsonResponse
    {
        /** @var \App\Domain\User\Models\User $user */
        $user = Auth::user();

        $response = [
            'message' => 'in development',
        ];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
