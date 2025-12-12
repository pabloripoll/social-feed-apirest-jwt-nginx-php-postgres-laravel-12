<?php

use App\Domain\Member\Controller\MemberAccountController;
use App\Domain\Member\Controller\MemberAuthController;
use App\Domain\Member\Controller\MemberProfileController;
use App\Domain\Member\Controller\MemberFeedPostController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/v1')->name('api-v1.')->group(function () {

    Route::prefix('/account')->name('member-account.')->group(function () {
        Route::post('/register', [MemberAuthController::class, 'register'])->name('register');
        Route::post('/login', [MemberAuthController::class, 'login'])->name('login');

        Route::middleware(['jwt', 'member'])->group(function () {
            Route::get('/settings', [MemberAccountController::class, 'readSettings'])->name('read-settings');
            Route::get('/access-logs', [MemberAccountController::class, 'listAccessLogs'])->name('read-access-logs');
            Route::get('/profile', [MemberProfileController::class, 'readProfile'])->name('read-profile');
            Route::get('/avatars', [MemberAccountController::class, 'listAvatar'])->name('list-avatars');
            Route::post('/avatars', [MemberAccountController::class, 'uploadAvatar'])->name('upload-avatar');
            Route::delete('/avatars/{avatar_id}/select', [MemberAccountController::class, 'selectAvatarById'])->name('select-avatar');
            Route::delete('/avatars/{avatar_id}', [MemberAccountController::class, 'deleteAvatarById'])->name('delete-avatar');

            Route::get('/notifications', [MemberAccountController::class, 'listNotifications'])->name('list-notifications');
            Route::put('/notifications/{notification_id}/read', [MemberAccountController::class, 'setNotificationRead'])->name('set-notification-read');
        });

        Route::prefix('/feed')->middleware(['jwt', 'member'])->name('feed.')->group(function () {
            Route::get('/posts', [MemberFeedPostController::class, 'listPosts'])->name('posts-listing');
            Route::get('/posts/sketch', [MemberFeedPostController::class, 'readSketchPost'])->name('post-read-sketch');
            Route::post('/posts', [MemberFeedPostController::class, 'createPost'])->name('post-create');
            Route::put('/posts/{post_uid}', [MemberFeedPostController::class, 'editPost'])->name('post-edit');
            Route::get('/posts/{post_uid}', [MemberFeedPostController::class, 'readPost'])->name('post-read');
            Route::patch('/posts/{post_uid}', [MemberFeedPostController::class, 'updatePost'])->name('post-update');
            Route::delete('/posts/{post_uid}', [MemberFeedPostController::class, 'deletePost'])->name('post-delete');
            Route::post('/posts/{post_uid}/media', [MemberFeedPostController::class, 'uploadPostMedia'])->name('post-media-upload');
            Route::delete('/posts/{post_uid}/media', [MemberFeedPostController::class, 'deletePostMedia'])->name('post-media-delete');
        });
    });

    Route::prefix('/members')->name('members.')->group(function () {
        Route::get('/', [MemberProfileController::class, 'listSections'])->name('list-sections');
        Route::get('/{member_uid}/profile', [MemberProfileController::class, 'readMemberProfile'])->name('read-profile');
        Route::get('/{member_uid}/posts', [MemberProfileController::class, 'listPosts'])->name('list-posts');
    });
});
