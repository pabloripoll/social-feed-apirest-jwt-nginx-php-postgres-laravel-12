<?php
/** @var \Tests\TestCase $this */

use App\Domain\Geo\Models\GeoContinent;
use App\Domain\Geo\Models\GeoRegion;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;

beforeEach(function () {
    Artisan::call('db:seed');
});

describe('Visit GEO index route - @GET /api/v1/geo', function () {
    it('succeeds every visitor can access to endpoint', function () {
        $route = route('api-v1.geo.index');
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            // Basic HTTP status + shape assertion using assertJsonStructure (concise & reliable)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'regions' => [
                        '*' => [
                            'id',
                            'name',
                            'continent_id',
                        ],
                    ],
                ],
            ]);
        // Additional content checks (optional, more explicit)
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        foreach ($data as $continent) {
            $this->assertArrayHasKey('id', $continent);
            $this->assertArrayHasKey('name', $continent);
            $this->assertArrayHasKey('regions', $continent);
            $this->assertIsArray($continent['regions']);

            foreach ($continent['regions'] as $region) {
                $this->assertArrayHasKey('id', $region);
                $this->assertArrayHasKey('name', $region);
                $this->assertArrayHasKey('continent_id', $region);

                // Ensure the relation ties back to the parent continent
                $this->assertEquals($continent['id'], $region['continent_id']);
            }
        }
    });
});

describe('List continents - @GET /api/v1/geo/continents', function () {
    it('succeeds every visitor can access and list all continents', function () {
        $route = route('api-v1.geo.continents-listing');
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                ],
            ]);
    });
});

describe('Read continent - @GET /api/v1/geo/continents/{continent_id}', function () {
    it('fails to read a continent by its id as it does not exist', function () {
        $route = route('api-v1.geo.continent-read', [
            'continent_id' => 123456
        ]);
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->etc()
            );
    });
});

describe('Read continent - @GET /api/v1/geo/continents/{continent_id}', function () {
    it('succeeds every visitor can access and read a continent by its id', function () {
        $continent = GeoContinent::first();
        $route = route('api-v1.geo.continent-read', [
            'continent_id' => $continent->id
        ]);
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('id', fn ($id) => is_int($id))
                ->where('name', fn ($name) => is_string($name))
                ->whereType('regions', 'array')
                ->etc()
            );
    });
});

describe('List continent regions - @GET /api/v1/geo/continents/{continent_id}/regions', function () {
    it('fails to read a continent by its id as it does not exist', function () {
        $route = route('api-v1.geo.regions-listing', [
            'continent_id' => 123456
        ]);
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->etc()
            );
    });
});

describe('List continent regions - @GET /api/v1/geo/continents/{continent_id}/regions', function () {
    it('succeeds every visitor can access and list all continent regionss', function () {
        $continent = GeoContinent::first();
        $route = route('api-v1.geo.regions-listing', [
            'continent_id' => $continent->id
        ]);
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            // top-level array of objects each containing id and name
            ->assertJsonStructure([
                '*' => ['id', 'name'],
            ])
            // assert types for each returned item
            ->assertJson(fn (AssertableJson $json) => $json
                ->each(fn (AssertableJson $region) => $region
                    ->whereType('id', 'integer')
                    ->whereType('name', 'string')
                    ->etc()
                )
            );
        // Optional: assert returned count matches DB
        $this->assertCount(
            GeoRegion::where('continent_id', $continent->id)->count(),
            $response->json()
        );
    });
});

describe('Read continent region - @GET /api/v1/geo/continents/{continent_id}/regions/{region_id}', function () {
    it('fails to read a continent by its id as it does not exist', function () {
        $route = route('api-v1.geo.region-read', [
            'continent_id' => 123456,
            'region_id' => 123456
        ]);
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->etc()
            );
    });
});

describe('Read continent region - @GET /api/v1/geo/continents/{continent_id}/regions/{region_id}', function () {
    it('fails to read a continent region by its id as it does not exist', function () {
        $region = GeoRegion::first();
        $route = route('api-v1.geo.region-read', [
            'continent_id' => $region->continent_id,
            'region_id' => 123456
        ]);
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->etc()
            );
    });
});

describe('Read continent region - @GET /api/v1/geo/continents/{continent_id}/regions/{region_id}', function () {
    it('succeeds every visitor can access and read continent region', function () {
        $region = GeoRegion::first();
        $route = route('api-v1.geo.region-read', [
            'continent_id' => $region->continent_id,
            'region_id' => $region->id
        ]);
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('id')
                ->has('name')
                ->etc()
            );
    });
});
