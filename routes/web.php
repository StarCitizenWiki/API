<?php

declare(strict_types=1);

use App\Http\Controllers\GameVersionSelectionController;
use App\Http\Controllers\Web\CommLinkController;
use App\Http\Controllers\Web\GalactapediaController;
use App\Http\Controllers\Web\ItemController;
use App\Http\Controllers\Web\ShipMatrixVehicleController;
use App\Http\Controllers\Web\StarmapCelestialObjectController;
use App\Http\Controllers\Web\StarmapStarsystemController;
use App\Http\Controllers\Web\StatController;
use App\Http\Controllers\Web\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/comm-links', [CommLinkController::class, 'index'])->name('web.comm-links.index');
Route::get('/comm-links/{id}', [CommLinkController::class, 'show'])->name('web.comm-links.show');

Route::get('/stats', [StatController::class, 'index'])->name('web.stats.index');

Route::get('/galactapedia', [GalactapediaController::class, 'index'])->name('web.galactapedia.index');
Route::get('/galactapedia/{article}', [GalactapediaController::class, 'show'])->name('web.galactapedia.show');

Route::get('/vehicles', [VehicleController::class, 'index'])->name('web.vehicles.index');
Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])->name('web.vehicles.show');

Route::get('/items', [ItemController::class, 'index'])->name('web.items.index');
Route::get('/items/{item}', [ItemController::class, 'show'])->whereUuid('item')->name('web.items.show');

Route::get('/ship-matrix/vehicles', [ShipMatrixVehicleController::class, 'index'])
    ->name('web.ship-matrix.vehicles.index');
Route::get('/ship-matrix/vehicles/{vehicle}', [ShipMatrixVehicleController::class, 'show'])
    ->name('web.ship-matrix.vehicles.show');
Route::get('/starmap/systems', [StarmapStarsystemController::class, 'index'])
    ->name('web.starmap.systems.index');
Route::get('/starmap/systems/{id}', [StarmapStarsystemController::class, 'show'])
    ->name('web.starmap.systems.show');
Route::get('/starmap/celestial-objects', [StarmapCelestialObjectController::class, 'index'])
    ->name('web.starmap.celestial-objects.index');
Route::get('/starmap/celestial-objects/{id}', [StarmapCelestialObjectController::class, 'show'])
    ->name('web.starmap.celestial-objects.show');

Route::post('/game-version', GameVersionSelectionController::class)
    ->name('game-version.select');
