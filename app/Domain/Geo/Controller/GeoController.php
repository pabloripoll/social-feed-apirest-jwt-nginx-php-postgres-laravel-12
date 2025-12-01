<?php

namespace App\Domain\Geo\Controller;

use App\Domain\Geo\Models\GeoContinent;
use App\Domain\Geo\Models\GeoRegion;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class GeoController
{
    /**
     * GET /api/v1/geo
     */
    public function index(Request $request): JsonResponse
    {
        $response = GeoContinent::select('id', 'name')
            ->with('regions:id,name,continent_id')
            ->orderBy('id')
            ->get();

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/geo/continents
     */
    public function listContinents(Request $request): JsonResponse
    {
        $response = GeoContinent::select('id', 'name')
            ->orderBy('id')
            ->get();

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/geo/continents/{continent_id}
     */
    public function readContinent(Request $request, int $continent_id): JsonResponse
    {
        $response = GeoContinent::select('id', 'name')
            ->where('id', $continent_id)
            ->with('regions:id,name,continent_id')
            ->orderBy('id')
            ->first();

        if (! $response) {
            return response()->json(['message' => 'Continent not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/geo/continents/{continent_id}/regions
     */
    public function listRegions(Request $request, int $continent_id): JsonResponse
    {
        $response = GeoRegion::select('id', 'name')
            ->where('continent_id', $continent_id)
            ->orderBy('id')
            ->get();

        if ($response->count() < 1) {
            return response()->json(['message' => 'Continent ID not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return response()->json($response, JsonResponse::HTTP_OK);
    }

    /**
     * GET /api/v1/geo/continents/{continent_id}/regions/{region_id}
     */
    public function readRegion(Request $request, int $continent_id, int $region_id): JsonResponse
    {
        $response = GeoRegion::select('id', 'name')
            ->where('id', $region_id)
            ->where('continent_id', $continent_id)
            ->first();

        if (! $response) {
            return response()->json(['message' => 'Region not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return response()->json($response, JsonResponse::HTTP_OK);
    }
}
