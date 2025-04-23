<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\GoogleController;

Route::get('/', function () {
    return view('auth.login');
});


Auth::routes();

Route::get('/home', [TaskController::class, 'index'])->name('tasks.index');

Route::resource('tasks', TaskController::class)->middleware('auth');

Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');

Route::post('/tasks/update-status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.auth');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::post('/tasks/{task}/sync', [TaskController::class, 'syncWithGoogle'])->name('tasks.sync');
