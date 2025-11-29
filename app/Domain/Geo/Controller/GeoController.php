<?php

namespace App\Domain\Geo\Controller;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class GeoController
{
    /**
     * GET /api/v1/geo
     */
    public function index(Request $request): JsonResponse
    {
        $response = ['message' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/geo/continents
     */
    public function listContinents(Request $request): JsonResponse
    {
        $response = ['message' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/geo/continents/{continent_id}
     */
    public function readContinent(Request $request, int $continent_id): JsonResponse
    {
        $response = ['message' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/geo/continents/{continent_id}/regions
     */
    public function listRegions(Request $request, int $continent_id): JsonResponse
    {
        $response = ['message' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/geo/continents/{continent_id}/regions/{region_id}
     */
    public function readRegion(Request $request, int $continent_id, int $region_id): JsonResponse
    {
        $response = ['message' => true];

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
