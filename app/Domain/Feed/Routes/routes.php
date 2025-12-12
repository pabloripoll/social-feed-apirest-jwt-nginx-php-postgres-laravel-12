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
});
