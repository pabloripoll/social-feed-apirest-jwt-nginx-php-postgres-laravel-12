<?php

use App\Domain\Feed\Controller\FeedController;
use App\Domain\Feed\Controller\FeedPostController;
use App\Domain\Feed\Controller\FeedReportController;
use App\Domain\Feed\Controller\FeedPostThumbController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/v1')->name('api-v1.')->group(function () {

    Route::prefix('/feed')->name('feed.')->group(function () {
        Route::get('/reports/types', [FeedController::class, 'reportsTypes'])->name('report-types');
        Route::get('/categories', [FeedController::class, 'categories'])->name('categories');

        Route::get('/posts', [FeedPostController::class, 'posts'])->name('posts');
        Route::get('/posts/following', [FeedPostController::class, 'followingMembersPosts'])->middleware(['jwt'])->name('posts-following');
        Route::get('/posts/{uid}', [FeedPostController::class, 'readPost'])->name('post-read');

        Route::middleware(['jwt', 'member'])->group(function () {
            Route::post('/posts/{uid}/reports', [FeedReportController::class, 'createReport'])->name('post-report-create');
            Route::get('/posts/{uid}/reports', [FeedReportController::class, 'readReport'])->name('post-report-read');
            Route::patch('/posts/{uid}/reports', [FeedReportController::class, 'updateReport'])->name('post-report-update');
            Route::delete('/posts/{uid}/reports', [FeedReportController::class, 'deleteReport'])->name('post-report-delete');

            Route::get('/posts/{uid}/thumbs', [FeedPostThumbController::class, 'readPostThumbs'])->name('post-thumbs-read');
            Route::post('/posts/{uid}/thumbs/up', [FeedPostThumbController::class, 'createThumbUp'])->name('post-thumbs-up-create');
            Route::delete('/posts/{uid}/thumbs/up', [FeedPostThumbController::class, 'deleteThumbUp'])->name('post-thumbs-up-delete');
            Route::post('/posts/{uid}/thumbs/down', [FeedPostThumbController::class, 'createThumbDown'])->name('post-thumbs-down-create');
            Route::delete('/posts/{uid}/thumbs/down', [FeedPostThumbController::class, 'deleteThumbDown'])->name('post-thumbs-down-delete');
        });
    });
});
