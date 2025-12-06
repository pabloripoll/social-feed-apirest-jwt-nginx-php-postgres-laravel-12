<?php

namespace App\Domain\Feed\Controller;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class FeedReportController
{
    /**
     * POST /api/v1/feed/posts/{post_id}/report
     */
    public function createReport(Request $request): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/feed/posts/{post_id}/report/{report_id}
     */
    public function readReport(Request $request, int $post_id, int $report_id): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * PATCH /api/v1/feed/posts/{post_id}/report/{report_id}
     */
    public function patchReport(Request $request, int $post_id, int $report_id): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * DELETE /api/v1/feed/posts/{post_id}/report/{report_id}
     */
    public function deleteReport(Request $request, int $post_id, int $report_id): JsonResponse
    {
        $response = ['test' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
