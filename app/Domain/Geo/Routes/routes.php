<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Geo\Controller\GeoController;

Route::prefix('/api/v1/geo')->name('api-v1.geo.')->group(function () {
    Route::get('/', [GeoController::class, 'index'])->name('index');
    Route::get('/continents', [GeoController::class, 'listContinents'])->name('continents-listing');
    Route::get('/continents/{continent_id}/', [GeoController::class, 'readContinent'])->name('continent-read');
    Route::get('/continents/{continent_id}/regions', [GeoController::class, 'listRegions'])->name('regions-listing');
    Route::get('/continents/{continent_id}/regions/{region_id}', [GeoController::class, 'readRegion'])->name('region-read');
});
