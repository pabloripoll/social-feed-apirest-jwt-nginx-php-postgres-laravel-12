<?php

use App\Domain\Feed\Controller\FeedController;
use App\Domain\Feed\Controller\FeedPostController;
use App\Domain\Feed\Controller\FeedReportController;
use App\Domain\Feed\Controller\FeedPostThumbController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/v1')->name('api-v1.')->group(function () {

    Route::prefix('/feed')->name('feed.')->group(function () {
        Route::get('/reports', [FeedController::class, 'reportsTypes'])->name('report-types');
        Route::get('/categories', [FeedController::class, 'categories'])->name('categories');

        Route::get('/posts', [FeedPostController::class, 'posts'])->name('posts');
        Route::get('/posts/{post_id}', [FeedPostController::class, 'readPost'])->name('post-read');
        Route::get('/posts/{post_id}/thumbs', [FeedPostController::class, 'listPostThumbs'])->name('post-thumbs-read');

        Route::middleware(['jwt', 'member'])->group(function () {
            Route::post('/posts/{post_id}/reports', [FeedReportController::class, 'createReport'])->name('report-create');

            Route::post('/posts/{post_id}/thumbs/up', [FeedPostThumbController::class, 'createThumbUp'])->name('post-thumbs-up-create');
            Route::delete('/posts/{post_id}/thumbs/up', [FeedPostThumbController::class, 'deleteThumbUp'])->name('post-thumbs-up-delete');
            Route::post('/posts/{post_id}/thumbs/down', [FeedPostThumbController::class, 'createThumbDown'])->name('post-thumbs-down-create');
            Route::delete('/posts/{post_id}/thumbs/down', [FeedPostThumbController::class, 'deleteThumbDown'])->name('post-thumbs-down-delete');
        });
    });
});
