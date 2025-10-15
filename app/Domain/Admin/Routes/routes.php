<?php

use App\Domain\Admin\Controller\AdminAccountController;
use App\Domain\Admin\Controller\AdminAuthController;
use App\Domain\Admin\Controller\AdminProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/v1/admin/auth')->name('admin-auth.')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login');
    Route::post('/refresh', [AdminAuthController::class, 'refresh'])->name('refresh');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware(['jwt', 'admin'])->group(function () {
        Route::post('/register', [AdminAuthController::class, 'register'])->name('register');
        Route::get('/whoami', [AdminAuthController::class, 'whoami'])->name('whoami');
    });
});

Route::prefix('/api/v1/admin/account')->name('admin-account.')->middleware(['jwt', 'admin'])->group(function () {
    Route::get('/', [AdminAccountController::class, 'listSections'])->name('list-sections');

    Route::get('/profile', [AdminAccountController::class, 'readProfile'])->name('read-profile');
    Route::patch('/profile', [AdminAccountController::class, 'updateProfile'])->name('update-profile');
    Route::post('/profile/avatar', [AdminAccountController::class, 'uploadAvatar'])->name('upload-avatar');
    Route::delete('/profile/avatar', [AdminAccountController::class, 'deleteAvatar'])->name('delete-avatar');

    Route::get('/posts', [AdminAccountController::class, 'listPosts'])->name('list-posts');

    Route::get('/notifications', [AdminAccountController::class, 'listNotifications'])->name('list-notifications');
    Route::put('/notifications/{notification_id}/read', [AdminAccountController::class, 'setNotificationRead'])->name('set-notification-read');
});

Route::prefix('/api/v1/admin/users')->name('admin-users.')->group(function () {
    Route::get('/', [AdminProfileController::class, 'listSections'])->name('list-sections');
    Route::get('/{admin_uid}/profile', [AdminProfileController::class, 'readProfile'])->name('read-profile');
    Route::get('/{admin_uid}/posts', [AdminProfileController::class, 'listPosts'])->name('list-posts');
});
