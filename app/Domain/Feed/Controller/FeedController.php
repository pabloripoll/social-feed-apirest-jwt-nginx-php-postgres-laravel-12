<?php

namespace App\Domain\Feed\Controller;

use App\Domain\Feed\Models\FeedCategory;
use App\Domain\Feed\Resources\FeedCategoryResource;
use App\Domain\Feed\Resources\FeedReportTypeResource;
use App\Domain\User\Models\UserModerationCategory;
use Symfony\Component\HttpFoundation\JsonResponse;

class FeedController
{
    /**
     * GET /api/v1/feed/reports
     */
    public function reportsTypes(): JsonResponse
    {
        $reportTypes = UserModerationCategory::orderBy('position', 'asc')->get();

        return response()->json(FeedReportTypeResource::collection($reportTypes), JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/feed/categories
     */
    public function categories(): JsonResponse
    {
        $categories = FeedCategory::orderBy('position', 'asc')->get();

        return response()->json(FeedCategoryResource::collection($categories), JsonResponse::HTTP_OK);
    }
}
