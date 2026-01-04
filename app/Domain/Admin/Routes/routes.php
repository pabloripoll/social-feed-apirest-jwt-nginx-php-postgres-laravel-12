<?php

use App\Domain\Admin\Controller\AdminAccessLogController;
use App\Domain\Admin\Controller\AdminAccountProfileController;
use App\Domain\Admin\Controller\AdminAccountSettingController;
use App\Domain\Admin\Controller\AdminAuthController;
use App\Domain\Admin\Controller\AdminAvatarController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/v1/admin')->name('api-v1.')->group(function () {
    Route::prefix('/account')->name('admin-account.')->group(function () {

        Route::post('/login', [AdminAuthController::class, 'login'])->name('login');

        Route::middleware(['jwt', 'admin'])->group(function () {
            Route::post('/register', [AdminAuthController::class, 'register'])->name('register');

            Route::get('/access-logs', [AdminAccessLogController::class, 'listing'])->name('access-logs-listing');
            Route::get('/settings', [AdminAccountSettingController::class, 'read'])->name('settings-read');
            Route::patch('/settings', [AdminAccountSettingController::class, 'update'])->name('settings-update');
            Route::get('/profile', [AdminAccountProfileController::class, 'read'])->name('profile-read');
            Route::patch('/profile', [AdminAccountProfileController::class, 'update'])->name('profile-update');
            Route::get('/avatars', [AdminAvatarController::class, 'list'])->name('avatars-list');
            Route::get('/avatars/selected', [AdminAvatarController::class, 'selected'])->name('avatars-selected');
            Route::get('/avatars/{avatar_uid}', [AdminAvatarController::class, 'read'])->name('avatars-read');
            Route::post('/avatars', [AdminAvatarController::class, 'upload'])->name('avatars-upload');
            Route::put('/avatars/{avatar_uid}/select', [AdminAvatarController::class, 'select'])->name('avatars-select');
            Route::delete('/avatars/{avatar_uid}', [AdminAvatarController::class, 'delete'])->name('avatars-delete');
        });
    });
});
