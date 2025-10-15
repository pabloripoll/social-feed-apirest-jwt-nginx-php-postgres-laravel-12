<?php

use App\Domain\Member\Controller\MemberAuthController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Version 1 Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function () {
    return response()->json(['message' => 'Connected to api.'], JsonResponse::HTTP_OK);
});

Route::get('/v1', function () {
    return response()->json(['message' => 'Connected to api version 1.'], JsonResponse::HTTP_OK);
});

Route::get('/v2', function () {
    return response()->json(['message' => 'Connected to api version 2.'], JsonResponse::HTTP_OK);
});

Route::middleware('jwt.auth')->group(function () {
    Route::get('/jwt/test', function() {
        return response()->json(['message' => 'Protected route.', JsonResponse::HTTP_OK]);
    });
});
