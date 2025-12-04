<?php

use Illuminate\Support\Facades\Route;
use App\Domain\User\Controller\UserAuthController;

Route::prefix('/api/v1')->name('api-v1.')->group(function () {

    Route::prefix('/auth')->middleware(['jwt'])->name('user-auth.')->group(function () {
        Route::get('/whoami', [UserAuthController::class, 'whoami'])->name('whoami');
    });
});
