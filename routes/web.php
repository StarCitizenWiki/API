<?php

declare(strict_types=1);

use App\Http\Controllers\GameVersionSelectionController;
use App\Http\Controllers\Web\Game\ItemController;
use App\Http\Controllers\Web\Game\VehicleController;
use App\Http\Controllers\Web\Rsi\CommLinkController;
use App\Http\Controllers\Web\StarCitizen\GalactapediaController;
use App\Http\Controllers\Web\StarCitizen\ShipMatrixVehicleController;
use App\Http\Controllers\Web\StarCitizen\Starmap\CelestialObjectController;
use App\Http\Controllers\Web\StarCitizen\Starmap\StarsystemController;
use App\Http\Controllers\Web\StarCitizen\StatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/comm-links', [CommLinkController::class, 'index'])->name('web.comm-links.index');
Route::get('/comm-links/search', [CommLinkController::class, 'search'])->name('web.comm-links.search');
Route::get('/comm-links/images', [CommLinkController::class, 'imagesIndex'])->name('web.comm-links.images.index');
Route::get('/comm-links/images/search', [CommLinkController::class, 'searchImagesByName'])->name('web.comm-links.images.search');
Route::post('/comm-links/images/reverse-search', [CommLinkController::class, 'reverseImageSearch'])
    ->middleware('throttle:reverse-image-search')
    ->name('web.comm-links.images.reverse-search');
Route::get('/comm-links/images/{image}', [CommLinkController::class, 'showImage'])->name('web.comm-links.images.show');
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
Route::get('/ship-matrix/ground-vehicles', [ShipMatrixVehicleController::class, 'index'])
    ->name('web.ship-matrix.ground-vehicles.index');

Route::get('/starmap/systems', [StarsystemController::class, 'index'])
    ->name('web.starmap.systems.index');
Route::get('/starmap/systems/{id}', [StarsystemController::class, 'show'])
    ->name('web.starmap.systems.show');
Route::get('/starmap/celestial-objects', [CelestialObjectController::class, 'index'])
    ->name('web.starmap.celestial-objects.index');
Route::get('/starmap/celestial-objects/{id}', [CelestialObjectController::class, 'show'])
    ->name('web.starmap.celestial-objects.show');

Route::post('/game-version', GameVersionSelectionController::class)
    ->name('game-version.select');

// Backwards compatibility

Route::redirect('/starcitizen/vehicles/ships', '/ship-matrix/vehicles', 301);
Route::redirect('/starcitizen/vehicles/ground-vehicles', '/ship-matrix/vehicles', 301);
