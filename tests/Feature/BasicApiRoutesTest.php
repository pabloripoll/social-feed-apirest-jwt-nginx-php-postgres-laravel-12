<?php

namespace Tests\Feature;

use Symfony\Component\HttpFoundation\JsonResponse;

it('public requests to @/api/ responds with 200 OK', function () {
    $this->get('/api')->assertStatus(JsonResponse::HTTP_OK);
});

it('public requests to @/api/v1/ responds with 200 OK', function () {
    $this->get('/api/v1/')->assertStatus(JsonResponse::HTTP_OK);
});

it('public requests to @/api/v2/ responds with 200 OK', function () {
    $this->get('/api/v2/')->assertStatus(JsonResponse::HTTP_OK);
});

it('public requests without header bearer token responds with 401 UNAUTHORIZED @/api/jwt/test', function () {
    $this->get('/api/jwt/test')->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
});