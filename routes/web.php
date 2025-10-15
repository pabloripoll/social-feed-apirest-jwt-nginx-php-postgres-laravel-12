<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/docs/api-docs.json', function () {
    $jsonPath = storage_path('api-docs/api-docs.json');
    if (! file_exists($jsonPath)) {
        abort(404, 'Swagger JSON not found');
    }
    return Response::file($jsonPath, [
        'Content-Type' => 'application/json'
    ]);
});