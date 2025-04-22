<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

Route::get('/', function () {
    return view('auth.login');
});


Auth::routes();

Route::get('/home', [TaskController::class, 'index'])->name('tasks.index');

Route::resource('tasks', TaskController::class)->middleware('auth');

Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');

Route::post('/tasks/update-status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');

