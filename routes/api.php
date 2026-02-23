<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::post('/posts/{id}/approve', [PostController::class, 'approve']);
    Route::post('/posts/{id}/reject', [PostController::class, 'reject']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
});
