<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Game\UnifiedSearchController;
use App\Http\Controllers\GameVersionSelectionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\Game\BlueprintController;
use App\Http\Controllers\Web\Game\ChangelogController;
use App\Http\Controllers\Web\Game\CommodityController;
use App\Http\Controllers\Web\Game\ItemController;
use App\Http\Controllers\Web\Game\MissionController;
use App\Http\Controllers\Web\Game\StarmapLocationController;
use App\Http\Controllers\Web\Game\VehicleController;
use App\Http\Controllers\Web\Rsi\CommLinkController;
use App\Http\Controllers\Web\StarCitizen\GalactapediaController;
use App\Http\Controllers\Web\StarCitizen\ShipMatrixVehicleController;
use App\Http\Controllers\Web\StarCitizen\Starmap\CelestialObjectController;
use App\Http\Controllers\Web\StarCitizen\Starmap\StarsystemController;
use App\Http\Controllers\Web\StarCitizen\StatController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', static function () {
    return view('welcome');
})->name('home');

Route::get('/comm-links', [CommLinkController::class, 'index'])->name('web.comm-links.index');
Route::get('/comm-links/search', [CommLinkController::class, 'search'])->name('web.comm-links.search');
Route::get('/comm-links/images', [CommLinkController::class, 'imagesIndex'])->name('web.comm-links.images.index');
Route::get('/comm-links/images/search', [CommLinkController::class, 'searchImagesByName'])->name('web.comm-links.images.search');
Route::post('/comm-links/images/reverse-search', [CommLinkController::class, 'reverseImageSearch'])
    ->middleware('throttle:reverse-image-search')
    ->name('web.comm-links.images.reverse-search');
Route::get('/comm-links/images/tag-{tag}', static function (): never {
    abort(404);
});
Route::get('/comm-links/images/{image}', [CommLinkController::class, 'showImage'])
    ->whereNumber('image')
    ->name('web.comm-links.images.show');
Route::get('/comm-links/images/{image}/similar', [CommLinkController::class, 'similarImages'])
    ->whereNumber('image')
    ->middleware(['auth', 'throttle:similar-image-search'])
    ->name('web.comm-links.images.similar');
Route::get('/comm-links/{id}', [CommLinkController::class, 'show'])->name('web.comm-links.show');

Route::get('/stats', [StatController::class, 'index'])->name('web.stats.index');

Route::get('/galactapedia', [GalactapediaController::class, 'index'])->name('web.galactapedia.index');
Route::get('/galactapedia/{article}', [GalactapediaController::class, 'show'])->name('web.galactapedia.show');

Route::get('/vehicles', [VehicleController::class, 'index'])->name('web.vehicles.index');
Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])->name('web.vehicles.show');

Route::get('/blueprints', [BlueprintController::class, 'index'])->name('web.blueprints.index');
Route::get('/blueprints/search', [BlueprintController::class, 'app'])->name('web.blueprints.search');
Route::get('/blueprints/{blueprint}', [BlueprintController::class, 'app'])
    ->name('web.blueprints.show');

Route::get('/items', [ItemController::class, 'index'])->name('web.items.index');
Route::get('/items/{item}', [ItemController::class, 'show'])->name('web.items.show');

Route::get('/commodities', [CommodityController::class, 'index'])->name('web.commodities.index');
Route::get('/commodities/{identifier}', [CommodityController::class, 'show'])->name('web.commodities.show');

Route::get('/missions', [MissionController::class, 'index'])->name('web.missions.index');
Route::get('/missions/{mission}', [MissionController::class, 'show'])
    ->name('web.missions.show');

Route::get('/changelog/{version}', [ChangelogController::class, 'show'])->name('web.changelog.show');

Route::get('/locations', [StarmapLocationController::class, 'index'])
    ->name('web.locations.index');
Route::get('/locations/{identifier}', [StarmapLocationController::class, 'show'])
    ->name('web.locations.show');

Route::get('/search/{query}', [UnifiedSearchController::class, 'resolve'])
    ->middleware('throttle:search')
    ->where('query', '[^/]+')
    ->name('web.search');

Route::get('/ship-matrix/vehicles', [ShipMatrixVehicleController::class, 'index'])
    ->name('web.ship-matrix.vehicles.index');
Route::get('/ship-matrix/ground-vehicles', [ShipMatrixVehicleController::class, 'index'])
    ->name('web.ship-matrix.ground-vehicles.index');

Route::get('/starmap/systems', [StarsystemController::class, 'index'])
    ->name('web.starmap.systems.index');
Route::get('/starmap/systems/{code}', [StarsystemController::class, 'show'])
    ->name('web.starmap.systems.show');
Route::get('/starmap/celestial-objects', [CelestialObjectController::class, 'index'])
    ->name('web.starmap.celestial-objects.index');
Route::get('/starmap/celestial-objects/{code}', [CelestialObjectController::class, 'show'])
    ->name('web.starmap.celestial-objects.show');

Route::post('/game-version', GameVersionSelectionController::class)
    ->name('game-version.select');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::post('/profile/token', [ProfileController::class, 'createToken'])->name('profile.token.create');
    Route::delete('/profile/token/{id}', [ProfileController::class, 'deleteToken'])->name('profile.token.delete');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Backwards compatibility

/**
 * Intentionally uses the framework-normalized query string so legacy redirects keep a canonical URL format.
 */
$legacyRedirect = static function (Request $request, string $target): RedirectResponse {
    $queryString = $request->getQueryString();

    if ($queryString !== null && $queryString !== '') {
        $target .= '?'.$queryString;
    }

    return redirect($target, 301);
};

Route::any('/starcitizen/vehicles/ships', static fn (Request $request): RedirectResponse => $legacyRedirect($request, '/ship-matrix/vehicles'));
Route::any('/starcitizen/vehicles/ground-vehicles', static fn (Request $request): RedirectResponse => $legacyRedirect($request, '/ship-matrix/vehicles'));
Route::any('/starcitizen/galactapedia', static fn (Request $request): RedirectResponse => $legacyRedirect($request, '/galactapedia'));
Route::any('/starcitizen/galactapedia/{article}', static fn (Request $request, string $article): RedirectResponse => $legacyRedirect($request, '/galactapedia/'.$article));
Route::any('/starcitizen/starmap/starsystems', static fn (Request $request): RedirectResponse => $legacyRedirect($request, '/starmap/systems'));
Route::any('/starcitizen/starmap/starsystems/{id}', [StarsystemController::class, 'legacyRedirect']);
Route::any('/starcitizen/starmap/celestial_objects', static fn (Request $request): RedirectResponse => $legacyRedirect($request, '/starmap/celestial-objects'));
Route::any('/starcitizen/starmap/celestial_objects/{id}', [CelestialObjectController::class, 'legacyRedirect']);
Route::any('/dashboard', static fn (Request $request): RedirectResponse => $legacyRedirect($request, '/'));
Route::any('/rsi/comm-links', static fn (Request $request): RedirectResponse => $legacyRedirect($request, '/comm-links'));
Route::any('/rsi/comm-links/search', static fn (Request $request): RedirectResponse => $legacyRedirect($request, '/comm-links/search'));
Route::any('/rsi/comm-links/images', static fn (Request $request): RedirectResponse => $legacyRedirect($request, '/comm-links/images'));
Route::any('/rsi/stats', static fn (Request $request): RedirectResponse => $legacyRedirect($request, '/stats'));
Route::any('/rsi/comm-links/{id}', static fn (Request $request, string $id): RedirectResponse => $legacyRedirect($request, '/comm-links/'.$id));
Route::any('/rsi/comm-links/images/{image}', static fn (Request $request, string $image): RedirectResponse => $legacyRedirect($request, '/comm-links/images/'.$image));
Route::any('/rsi/comm-links/images/{image}/similar', static fn (Request $request, string $image): RedirectResponse => $legacyRedirect($request, '/comm-links/images/'.$image.'/similar'));
