<?php

use App\Domain\Post\Controller\FeedController;
use App\Domain\Post\Controller\PostController;
use App\Domain\Post\Controller\PostVoteController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/v1/feed')->name('feed.')->group(function () {

    Route::get('/', [FeedController::class, 'listPosts'])->name('list-posts');

    Route::get('/reports', [FeedController::class, 'listReports'])->name('list-report-types');

    Route::get('/categories', [FeedController::class, 'listCategories'])->name('list-post-categories');

    Route::get('/posts/{post_id}', [FeedController::class, 'readPost'])->name('read-post');

    Route::get('/posts/{post_id}/votes', [FeedController::class, 'listPostVotes'])->name('list-post-votes');

    Route::middleware(['jwt', 'member'])->group(function () {

        Route::post('/posts', [PostController::class, 'createPost'])->name('create-post');
        Route::put('/posts/{post_id}', [PostController::class, 'setPost'])->name('set-post');
        Route::patch('/posts/{post_id}', [PostController::class, 'updatePost'])->name('update-post');
        Route::delete('/posts/{post_id}', [PostController::class, 'deletePost'])->name('delete-post');
        Route::post('/posts/{post_id}/media', [PostController::class, 'uploadPostMedia'])->name('upload-post-media');
        Route::delete('/posts/{post_id}/media', [PostController::class, 'deletePostMedia'])->name('delete-post-media');
        Route::post('/posts/{post_id}/report', [PostController::class, 'createPostReport'])->name('create-post-report');

        Route::post('/posts/{post_id}/votes/up', [PostVoteController::class, 'createVoteUp'])->name('create-post-vote-up');
        Route::delete('/posts/{post_id}/votes/up', [PostVoteController::class, 'deleteVoteUp'])->name('delete-post-vote-up');
        Route::post('/posts/{post_id}/votes/down', [PostVoteController::class, 'createVoteDown'])->name('create-post-vote-down');
        Route::delete('/posts/{post_id}/votes/down', [PostVoteController::class, 'deleteVoteDown'])->name('delete-post-vote-down');
    });
});
