<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\GoogleController;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::get('/home', [TaskController::class, 'index'])->name('home'); // nome diferente!

Route::resource('tasks', TaskController::class)->middleware('auth');

Route::post('/tasks/update-status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');

Route::post('/tasks/{task}/sync', [TaskController::class, 'syncWithGoogle'])->name('tasks.sync');

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.auth');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
