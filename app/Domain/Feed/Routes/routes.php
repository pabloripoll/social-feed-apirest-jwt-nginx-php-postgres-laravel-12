<?php

use App\Domain\Feed\Controller\FeedController;
use App\Domain\Feed\Controller\FeedPostController;
use App\Domain\Feed\Controller\FeedReportController;
use App\Domain\Feed\Controller\FeedThumbController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/v1')->name('api-v1.')->group(function () {

    Route::prefix('/feed')->name('feed.')->group(function () {
        Route::get('/', [FeedController::class, 'listPosts'])->name('posts');
        Route::get('/categories', [FeedController::class, 'listCategories'])->name('post-categories');
        Route::get('/reports', [FeedReportController::class, 'listReportsTypes'])->name('report-types');

        Route::get('/posts/{post_id}', [FeedController::class, 'readPost'])->name('post');
        Route::get('/posts/{post_id}/thumbs', [FeedController::class, 'listPostThumbs'])->name('post-thumbs');
        Route::get('/posts/{post_id}/visits', [FeedController::class, 'listPostThumbs'])->name('post-visits');

        Route::middleware(['jwt', 'member'])->group(function () {
            Route::post('/posts/{post_id}/reports', [FeedPostController::class, 'createPostReport'])->name('post-report-create');
            Route::post('/posts/{post_id}/thumbs/up', [FeedThumbController::class, 'createThumbUp'])->name('post-thumbs-up-create');
            Route::delete('/posts/{post_id}/thumbs/up', [FeedThumbController::class, 'deleteThumbUp'])->name('post-thumbs-up-delete');
            Route::post('/posts/{post_id}/thumbs/down', [FeedThumbController::class, 'createThumbDown'])->name('post-thumbs-down-create');
            Route::delete('/posts/{post_id}/thumbs/down', [FeedThumbController::class, 'deleteThumbDown'])->name('post-thumbs-down-delete');
        });
    });

    Route::prefix('/account/feed')->middleware(['jwt', 'member'])->name('account-feed.')->group(function () {
        Route::get('/posts', [FeedPostController::class, 'listPosts'])->name('posts-listing');
        Route::get('/posts/sketch', [FeedPostController::class, 'readSketchPost'])->name('post-read-sketch');
        Route::post('/posts', [FeedPostController::class, 'createPost'])->name('post-create');
        Route::put('/posts/{post_uid}', [FeedPostController::class, 'editPost'])->name('post-edit');
        Route::get('/posts/{post_uid}', [FeedPostController::class, 'readPost'])->name('post-read');
        Route::patch('/posts/{post_uid}', [FeedPostController::class, 'updatePost'])->name('post-update');
        Route::delete('/posts/{post_uid}', [FeedPostController::class, 'deletePost'])->name('post-delete');
        Route::post('/posts/{post_uid}/media', [FeedPostController::class, 'uploadPostMedia'])->name('post-media-upload');
        Route::delete('/posts/{post_uid}/media', [FeedPostController::class, 'deletePostMedia'])->name('post-media-delete');
    });
});
