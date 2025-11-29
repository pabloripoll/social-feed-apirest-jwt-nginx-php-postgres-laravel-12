<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Feed\Controller\FeedController;
use App\Domain\Feed\Controller\FeedPostController;
use App\Domain\Feed\Controller\FeedVoteController;
use App\Domain\Feed\Controller\FeedReportController;

Route::prefix('/api/v1')->name('api-v1.')->group(function () {

    Route::prefix('/feed')->name('feed.')->group(function () {
        Route::get('/', [FeedController::class, 'listPosts'])->name('posts');
        Route::get('/categories', [FeedController::class, 'listCategories'])->name('post-categories');
        Route::get('/reports', [FeedReportController::class, 'listReportsTypes'])->name('report-types');

        Route::get('/posts/{post_id}', [FeedController::class, 'readPost'])->name('post');
        Route::get('/posts/{post_id}/votes', [FeedController::class, 'listPostVotes'])->name('post-votes');
        Route::get('/posts/{post_id}/visits', [FeedController::class, 'listPostVotes'])->name('post-visits');

        Route::middleware(['jwt', 'member'])->group(function () {
            Route::post('/posts/{post_id}/reports', [FeedPostController::class, 'createPostReport'])->name('post-report-create');
            Route::post('/posts/{post_id}/votes/up', [FeedVoteController::class, 'createVoteUp'])->name('post-vote-up-create');
            Route::delete('/posts/{post_id}/votes/up', [FeedVoteController::class, 'deleteVoteUp'])->name('post-vote-up-delete');
            Route::post('/posts/{post_id}/votes/down', [FeedVoteController::class, 'createVoteDown'])->name('post-vote-down-create');
            Route::delete('/posts/{post_id}/votes/down', [FeedVoteController::class, 'deleteVoteDown'])->name('post-vote-down-delete');
        });
    });
});
