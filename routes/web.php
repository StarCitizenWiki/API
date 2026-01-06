<?php

declare(strict_types=1);

use App\Http\Controllers\GameVersionSelectionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::post('/game-version', GameVersionSelectionController::class)
    ->name('game-version.select');
