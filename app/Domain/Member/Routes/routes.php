<?php

use App\Domain\Member\Controller\MemberAccessLogController;
use App\Domain\Member\Controller\MemberAccountProfileController;
use App\Domain\Member\Controller\MemberAccountSettingController;
use App\Domain\Member\Controller\MemberAuthController;
use App\Domain\Member\Controller\MemberAvatarController;
use App\Domain\Member\Controller\MemberProfileController;
use App\Domain\Member\Controller\MemberFollowerController;
use App\Domain\Member\Controller\MemberFeedPostController;
use App\Domain\Member\Controller\MemberFeedMediaController;
use App\Domain\Member\Controller\MemberNotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/v1')->name('api-v1.')->group(function () {

    Route::prefix('/account')->name('member-account.')->group(function () {
        Route::post('/register', [MemberAuthController::class, 'register'])->name('register');
        Route::post('/login', [MemberAuthController::class, 'login'])->name('login');

        Route::middleware(['jwt', 'member'])->group(function () {
            Route::get('/access-logs', [MemberAccessLogController::class, 'listing'])->name('access-logs-listing');
            Route::get('/settings', [MemberAccountSettingController::class, 'read'])->name('settings-read');
            Route::patch('/settings', [MemberAccountSettingController::class, 'update'])->name('settings-update');
            Route::get('/profile', [MemberAccountProfileController::class, 'read'])->name('profile-read');
            Route::patch('/profile', [MemberAccountProfileController::class, 'update'])->name('profile-update');
            Route::get('/avatars', [MemberAvatarController::class, 'list'])->name('avatars-list');
            Route::get('/avatars/selected', [MemberAvatarController::class, 'selected'])->name('avatars-selected');
            Route::get('/avatars/{avatar_uid}', [MemberAvatarController::class, 'read'])->name('avatars-read');
            Route::post('/avatars', [MemberAvatarController::class, 'upload'])->name('avatars-upload');
            Route::put('/avatars/{avatar_uid}/select', [MemberAvatarController::class, 'select'])->name('avatars-select');
            Route::delete('/avatars/{avatar_uid}', [MemberAvatarController::class, 'delete'])->name('avatars-delete');
            Route::get('/notifications', [MemberNotificationController::class, 'listing'])->name('notifications-listing');
            Route::get('/notifications/{uid}/read', [MemberNotificationController::class, 'read'])->name('notifications-read');
            Route::post('/notifications/{uid}/mark/read', [MemberNotificationController::class, 'markAsRead'])->name('notifications-mark-as-read');
            Route::post('/notifications/{uid}/mark/unread', [MemberNotificationController::class, 'markAsRead'])->name('notifications-mark-as-unread');
            Route::delete('/notifications/{uid}/delete', [MemberNotificationController::class, 'read'])->name('notifications-delete');
        });

        Route::prefix('/feed')->middleware(['jwt', 'member'])->name('feed.')->group(function () {
            Route::get('/posts', [MemberFeedPostController::class, 'posts'])->name('posts-listing');
            Route::get('/posts/sketch', [MemberFeedPostController::class, 'readSketchPost'])->name('post-read-sketch');
            Route::post('/posts', [MemberFeedPostController::class, 'createPost'])->name('post-create');
            Route::put('/posts/{post_uid}', [MemberFeedPostController::class, 'editPost'])->name('post-edit');
            Route::get('/posts/{post_uid}', [MemberFeedPostController::class, 'readPost'])->name('post-read');
            Route::patch('/posts/{post_uid}', [MemberFeedPostController::class, 'updatePost'])->name('post-update');
            Route::delete('/posts/{post_uid}', [MemberFeedPostController::class, 'deletePost'])->name('post-delete');
            Route::get('/posts/{post_uid}/media', [MemberFeedMediaController::class, 'readPostMedia'])->name('post-media-list');
            Route::get('/posts/{post_uid}/media/{media_uid}', [MemberFeedMediaController::class, 'readPostMedia'])->name('post-media-read');
            Route::post('/posts/{post_uid}/media', [MemberFeedMediaController::class, 'uploadPostMedia'])->name('post-media-upload');
            Route::delete('/posts/{post_uid}/media/all', [MemberFeedMediaController::class, 'deletePostMedia'])->name('post-media-delete-all');
            Route::delete('/posts/{post_uid}/media/{media_uid}', [MemberFeedMediaController::class, 'deletePostMedia'])->name('post-media-delete-uid');
        });
    });

    Route::prefix('/members')->name('members.')->group(function () {
        Route::get('/{member_uid}/profile', [MemberProfileController::class, 'read'])->name('profile-read');
        Route::get('/{member_uid}/feed/posts', [MemberProfileController::class, 'feedPosts'])->name('profile-posts');
        Route::middleware(['jwt', 'member'])->group(function () {
            Route::post('/{member_uid}/follow', [MemberFollowerController::class, 'follow'])->name('profile-follow');
            Route::post('/{member_uid}/unfollow', [MemberFollowerController::class, 'unfollow'])->name('profile-unfollow');
        });
    });
});
