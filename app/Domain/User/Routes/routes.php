<?php

use Illuminate\Support\Facades\Route;
use App\Domain\User\Controller\UserAuthController;
use App\Domain\User\Controller\UserAdminController;
use App\Domain\User\Controller\UserMemberController;

Route::prefix('/api/v1')->name('api-v1.')->group(function () {

    Route::prefix('/auth')->name('auth.')->group(function () {
        Route::post('/activation', [UserAuthController::class, 'activation'])->name('activation');
        Route::post('/refresh', [UserAuthController::class, 'refresh'])->name('refresh');
        Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');
        Route::middleware(['jwt'])->group(function () {
            Route::get('/whoami', [UserAuthController::class, 'whoami'])->name('whoami');
        });
    });

    Route::prefix('/users')->middleware(['jwt', 'admin'])->name('users.')->group(function () {
        Route::prefix('/admins')->name('admins.')->group(function () {
            Route::get('/', [UserAdminController::class, 'listing'])->name('listing');
            Route::get('/{uid}', [UserAdminController::class, 'baseData'])->name('base-data');
            Route::get('/{uid}/profile', [UserAdminController::class, 'profile'])->name('profile-read');
        });
        Route::prefix('/members')->name('members.')->group(function () {
            Route::get('/', [UserMemberController::class, 'listing'])->name('listing');
            Route::get('/{uid}', [UserMemberController::class, 'baseData'])->name('base-data');
            Route::get('/{uid}/profile', [UserMemberController::class, 'profile'])->name('profile-read');
        });
    });

});
