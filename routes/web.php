<?php

declare(strict_types=1);

use App\Http\Controllers\GameVersionSelectionController;
use App\Http\Controllers\Web\CommLinkController;
use App\Http\Controllers\Web\GalactapediaController;
use App\Http\Controllers\Web\ShipMatrixVehicleController;
use App\Http\Controllers\Web\StatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/comm-links', [CommLinkController::class, 'index'])->name('web.comm-links.index');
Route::get('/comm-links/{id}', [CommLinkController::class, 'show'])->name('web.comm-links.show');
Route::get('/stats', [StatController::class, 'index'])->name('web.stats.index');
Route::get('/galactapedia', [GalactapediaController::class, 'index'])->name('web.galactapedia.index');
Route::get('/galactapedia/{article}', [GalactapediaController::class, 'show'])->name('web.galactapedia.show');
Route::get('/ship-matrix/ships', [ShipMatrixVehicleController::class, 'index'])
    ->name('web.ship-matrix.vehicles.index');
Route::get('/ship-matrix/vehicles/{vehicle}', [ShipMatrixVehicleController::class, 'show'])
    ->name('web.ship-matrix.vehicles.show');

Route::post('/game-version', GameVersionSelectionController::class)
    ->name('game-version.select');
