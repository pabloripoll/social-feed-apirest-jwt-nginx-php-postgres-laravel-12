<?php

use App\Domain\Member\Controller\MemberAccountController;
use App\Domain\Member\Controller\MemberAuthController;
use App\Domain\Member\Controller\MemberProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/v1')->name('api-v1.')->group(function () {

    Route::prefix('/account')->name('member-account.')->group(function () {
        Route::post('/register', [MemberAuthController::class, 'register'])->name('register');
        Route::post('/login', [MemberAuthController::class, 'login'])->name('login');

        Route::middleware(['jwt', 'member'])->group(function () {

            Route::get('/profile', [MemberAccountController::class, 'readProfile'])->name('read-profile');
            Route::patch('/profile', [MemberAccountController::class, 'updateProfile'])->name('update-profile');
            Route::post('/profile/avatar', [MemberAccountController::class, 'uploadAvatar'])->name('upload-avatar');
            Route::delete('/profile/avatar', [MemberAccountController::class, 'deleteAvatar'])->name('delete-avatar');

            Route::get('/notifications', [MemberAccountController::class, 'listNotifications'])->name('list-notifications');
            Route::put('/notifications/{notification_id}/read', [MemberAccountController::class, 'setNotificationRead'])->name('set-notification-read');

        });
    });

    Route::prefix('/members')->name('members.')->group(function () {
        Route::get('/', [MemberProfileController::class, 'listSections'])->name('list-sections');
        Route::get('/{member_uid}/profile', [MemberProfileController::class, 'readProfile'])->name('read-profile');
        Route::get('/{member_uid}/posts', [MemberProfileController::class, 'listPosts'])->name('list-posts');
    });

});
