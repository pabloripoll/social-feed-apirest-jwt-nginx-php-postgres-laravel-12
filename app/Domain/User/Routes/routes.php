<?php

use Illuminate\Support\Facades\Route;
use App\Domain\User\Controller\UserAuthController;
use App\Domain\User\Controller\UserAdminController;
use App\Domain\User\Controller\UserMemberController;
use App\Domain\User\Controller\UserModerationController;
use App\Domain\User\Controller\UserModerationSanctionController;
use App\Domain\User\Controller\UserModerationCategoryController;

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
            Route::get('/{id}', [UserAdminController::class, 'read'])->name('read');
            Route::get('/{id}/profile', [UserAdminController::class, 'profile'])->name('profile-read');
        });
        Route::prefix('/members')->name('members.')->group(function () {
            Route::get('/', [UserMemberController::class, 'listing'])->name('listing');
            Route::get('/{id}', [UserMemberController::class, 'read'])->name('read');
            Route::get('/{id}/profile', [UserMemberController::class, 'profile'])->name('profile-read');
        });
    });

    Route::prefix('/moderations')->middleware(['jwt', 'admin'])->name('moderations.')->group(function () {
        Route::get('/categories', [UserModerationCategoryController::class, 'listing'])->name('categories-listing');
        Route::post('/categories', [UserModerationCategoryController::class, 'create'])->name('categories-create');
        Route::get('/categories/{id}', [UserModerationCategoryController::class, 'read'])->name('categories-read');
        Route::patch('/categories/{id}', [UserModerationCategoryController::class, 'update'])->name('categories-update');
        Route::delete('/categories/{id}', [UserModerationCategoryController::class, 'delete'])->name('categories-delete');

        Route::get('/sanctions', [UserModerationSanctionController::class, 'listing'])->name('sanctions-listing');
        Route::post('/sanctions', [UserModerationSanctionController::class, 'create'])->name('sanctions-create');
        Route::get('/sanctions/{id}', [UserModerationSanctionController::class, 'read'])->name('sanctions-read');
        Route::patch('/sanctions/{id}', [UserModerationSanctionController::class, 'update'])->name('sanctions-update');
        Route::delete('/sanctions/{id}', [UserModerationSanctionController::class, 'delete'])->name('sanctions-delete');

        Route::get('/', [UserModerationController::class, 'listing'])->name('listing');
        Route::get('/filters', [UserModerationController::class, 'filters'])->name('filters');
        Route::get('/{id}', [UserModerationController::class, 'read'])->name('read');
        Route::patch('/{id}', [UserModerationController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserModerationController::class, 'delete'])->name('delete');

        Route::post('/{id}/messages', [UserModerationController::class, 'messages-create'])->name('messages-create');
    });
});
