<?php

use App\Domain\Admin\Controller\AdminAccountController;
use App\Domain\Admin\Controller\AdminAuthController;
use App\Domain\Admin\Controller\AdminProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/v1/admin')->name('api-v1.')->group(function () {

    Route::prefix('/account')->name('admin-account.')->group(function () {

        Route::post('/login', [AdminAuthController::class, 'login'])->name('login');

        Route::middleware(['jwt', 'admin'])->group(function () {

            Route::post('/register', [AdminAuthController::class, 'register'])->name('register');

            Route::get('/profile', [AdminAccountController::class, 'readProfile'])->name('read-profile');
            Route::patch('/profile', [AdminAccountController::class, 'updateProfile'])->name('update-profile');
            Route::post('/profile/avatar', [AdminAccountController::class, 'uploadAvatar'])->name('upload-avatar');
            Route::delete('/profile/avatar', [AdminAccountController::class, 'deleteAvatar'])->name('delete-avatar');

            Route::get('/notifications', [AdminAccountController::class, 'listNotifications'])->name('list-notifications');
            Route::put('/notifications/{notification_id}/read', [AdminAccountController::class, 'setNotificationRead'])->name('set-notification-read');
        });
    });

    Route::prefix('/users')->middleware(['jwt', 'admin'])->name('users.')->group(function () {
        Route::get('/', [AdminProfileController::class, 'listSections'])->name('list-sections');
        Route::get('/{admin_uid}/profile', [AdminProfileController::class, 'readProfile'])->name('read-profile');
        Route::get('/{admin_uid}/posts', [AdminProfileController::class, 'listPosts'])->name('list-posts');
    });

});
