<?php

use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;

/** @var \Tests\TestCase $this */
beforeEach(function () {
    //
});

describe('Visit GEO index route - @GET /api/v1/geo', function () {
    it('succeeds every visitor can access to endpoint', function () {
        $route = route('api-v1.geo.index');
        /** @var \Tests\TestCase $this */
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->etc()
            );
    });
});

describe('List continents - @GET /api/v1/geo/continents', function () {
    it('succeeds every visitor can access and list all continents', function () {
        $route = route('api-v1.geo.continents-listing');
        /** @var \Tests\TestCase $this */
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->etc()
            );
    });
});

describe('Read continent - @GET /api/v1/geo/continents/{continent_id}', function () {
    it('succeeds every visitor can access and read a continent by its id', function () {
        $route = route('api-v1.geo.continent-read', [
            'continent_id' => 1
        ]);
        /** @var \Tests\TestCase $this */
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->etc()
            );
    });
});

describe('List continent regions - @GET /api/v1/geo/continents/{continent_id}/regions', function () {
    it('succeeds every visitor can access and list all continent regionss', function () {
        $route = route('api-v1.geo.regions-listing', [
            'continent_id' => 1
        ]);
        /** @var \Tests\TestCase $this */
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->etc()
            );
    });
});

describe('Read continent region - @GET /api/v1/geo/continents/{continent_id}/regions/{region_id}', function () {
    it('succeeds every visitor can access and read continent region', function () {
        $route = route('api-v1.geo.region-read', [
            'continent_id' => 1,
            'region_id' => 1,
        ]);
        /** @var \Tests\TestCase $this */
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->etc()
            );
    });
});
