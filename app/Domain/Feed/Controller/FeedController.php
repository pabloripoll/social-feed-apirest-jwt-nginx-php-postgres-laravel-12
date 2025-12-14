<?php

namespace App\Domain\Feed\Controller;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\Feed\Models\FeedCategory;
use App\Domain\Feed\Models\FeedPost;
use App\Domain\Feed\Models\FeedReportType;
use App\Domain\Feed\Requests\FeedPostRequest;
use App\Domain\Feed\Resources\FeedPostResource;
use App\Domain\Feed\Resources\FeedCategoryResource;
use App\Domain\Feed\Resources\FeedReportTypeResource;
use App\Support\Paginate;
use Illuminate\Support\Facades\Validator;
use App\Domain\Feed\Service\FeedPostService;

class FeedController
{
    /**
     * GET /api/v1/feed/reports
     */
    public function reportsTypes(): JsonResponse
    {
        $reportTypes = FeedReportType::orderBy('position', 'asc')->get();

        return response()->json(FeedReportTypeResource::collection($reportTypes), JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/feed/categories
     */
    public function categories(): JsonResponse
    {
        $categories = FeedCategory::orderBy('position', 'asc')->get();

        return response()->json(FeedCategoryResource::collection($categories), JsonResponse::HTTP_CREATED);
    }
}
