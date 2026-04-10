<?php

namespace App\Modules\Admin\Controller;

use App\Modules\Admin\Models\AdminAccessLog;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminAccessLogController
{
    /**
     * GET /api/v1/account/access-logs
     */
    public function listing(): JsonResponse
    {
        /** @var Illuminate\Auth\AuthManager $user */
        $user = Auth::user();

        $logs = AdminAccessLog::query()
            ->where('user_id', $user->id)
            ->select('ip_address', 'user_agent', 'created_at', 'expires_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($log) => [
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at' => $log->created_at?->format('Y-m-d H:i:s'),
                'expires_at' => $log->expires_at?->format('Y-m-d H:i:s'),
            ]);

        return response()->json(
            [
                'page' => 1,
                'result' => $logs,
            ],
            JsonResponse::HTTP_OK
        );
    }
}
