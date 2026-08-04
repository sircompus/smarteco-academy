<?php

use App\Http\Controllers\Student\CommunityCommentController;
use App\Http\Controllers\Student\CommunityLikeController;
use App\Http\Controllers\Student\CommunityModerationController;
use App\Http\Controllers\Student\CommunityPostController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('community')
    ->name('student.community.')
    ->group(function (): void {
        Route::get('/', [
            CommunityPostController::class,
            'index',
        ])->name('index');

        Route::post('/posts', [
            CommunityPostController::class,
            'store',
        ])->name('posts.store');

        Route::get('/posts/{communityPost}/edit', [
            CommunityPostController::class,
            'edit',
        ])->name('posts.edit');

        Route::match(
            ['put', 'patch'],
            '/posts/{communityPost}',
            [
                CommunityPostController::class,
                'update',
            ]
        )->name('posts.update');

        Route::delete('/posts/{communityPost}', [
            CommunityPostController::class,
            'destroy',
        ])->name('posts.destroy');

        Route::post('/posts/{communityPost}/comments', [
            CommunityCommentController::class,
            'store',
        ])->name('comments.store');

        Route::delete(
            '/posts/{communityPost}/comments/{communityComment}',
            [
                CommunityCommentController::class,
                'destroy',
            ]
        )->name('comments.destroy');

        Route::post('/posts/{communityPost}/like', [
            CommunityLikeController::class,
            '__invoke',
        ])->name('likes.toggle');

        Route::patch('/moderation/posts/{communityPost}', [
            CommunityModerationController::class,
            'post',
        ])->name('moderation.posts');

        Route::patch('/moderation/comments/{communityComment}', [
            CommunityModerationController::class,
            'comment',
        ])->name('moderation.comments');
    });
