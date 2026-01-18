<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FailedJobController;
use App\Http\Controllers\Admin\GameVersionController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Users Management
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

// Failed Jobs Management
Route::get('/jobs', [FailedJobController::class, 'index'])->name('jobs.index');
Route::delete('/jobs/{id}', [FailedJobController::class, 'destroy'])->name('jobs.destroy');
Route::delete('/jobs', [FailedJobController::class, 'truncate'])->name('jobs.truncate');

// Game Versions Management
Route::get('/game-versions', [GameVersionController::class, 'index'])->name('game-versions.index');
Route::post('/game-versions/{gameVersion}/set-default', [GameVersionController::class, 'setDefault'])->name('game-versions.set-default');

// Translations Management
Route::get('/translations', [TranslationController::class, 'index'])->name('translations.index');
Route::get('/translations/{type}/{id}/edit', [TranslationController::class, 'edit'])
    ->where('type', 'comm-link|article|smSize|smFocus|smType')
    ->name('translations.edit');
Route::put('/translations/{type}/{id}', [TranslationController::class, 'update'])
    ->where('type', 'comm-link|article|smSize|smFocus|smType')
    ->name('translations.update');
