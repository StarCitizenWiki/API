<?php

use App\Http\Controllers\Api\Game\BlueprintController;
use App\Http\Controllers\Api\Game\CommodityController;
use App\Http\Controllers\Api\Game\FactionController;
use App\Http\Controllers\Api\Game\GameVersionController;
use App\Http\Controllers\Api\Game\ItemController;
use App\Http\Controllers\Api\Game\ManufacturerController;
use App\Http\Controllers\Api\Game\MissionController;
use App\Http\Controllers\Api\Game\StarmapLocationController;
use App\Http\Controllers\Api\Game\UnifiedSearchController;
use App\Http\Controllers\Api\Game\VehicleController;
use App\Http\Controllers\Api\Game\VersionChangelogController;
use App\Http\Controllers\Api\Rsi\CommLink\CommLinkController;
use App\Http\Controllers\Api\Rsi\CommLink\CommLinkSearchController;
use App\Http\Controllers\Api\Rsi\CommLink\ImageController;
use App\Http\Controllers\Api\StarCitizen\GalactapediaController;
use App\Http\Controllers\Api\StarCitizen\Starmap\CelestialObjectController;
use App\Http\Controllers\Api\StarCitizen\Starmap\StarsystemController;
use App\Http\Controllers\Api\StarCitizen\StatController;
use App\Http\Controllers\Api\StarCitizen\VehicleController as ShipMatrixVehicleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/user', static function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/v2/openapi', static function () {
    return response(
        File::get(base_path('swagger.yaml'))
    )->header('Content-Type', 'application/yaml');
});

Route::get('/openapi', static function () {
    return response(
        File::get(base_path('swagger.yaml'))
    )->header('Content-Type', 'application/yaml');
});

Route::group(
    [],
    static function () {
        Route::middleware(['game.version', 'limit.parameter'])->group(static function () {
            Route::get('search', [UnifiedSearchController::class, 'search'])->middleware('throttle:search')->name('search');
            Route::get('search/{query}', [UnifiedSearchController::class, 'apiResolve'])->middleware('throttle:search')->where('query', '[^/]+')->name('resolve');

            Route::prefix('v2')->group(static function () {
                Route::get('vehicles', [VehicleController::class, 'index'])
                    ->defaults('api_version', 'v2')
                    ->name('v2.vehicles.index');
                Route::post('vehicles/search', [VehicleController::class, 'search'])
                    ->defaults('api_version', 'v2')
                    ->name('v2.vehicles.search');
                Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])
                    ->defaults('api_version', 'v2')
                    ->where('vehicle', '.*')
                    ->name('v2.vehicles.show');

                Route::any('{any?}', static function (Request $request, ?string $any = null) {
                    $target = '/api'.($any !== null && $any !== '' ? '/'.$any : '');
                    $queryString = $request->getQueryString();

                    if ($queryString !== null && $queryString !== '') {
                        $target .= '?'.$queryString;
                    }

                    return redirect($target, 308);
                })->where('any', '.*');
            });

            Route::prefix('v3')->group(static function () {
                Route::get('vehicles', [VehicleController::class, 'index'])
                    ->defaults('api_version', 'v3')
                    ->name('v3.vehicles.index');
                Route::post('vehicles/search', [VehicleController::class, 'search'])
                    ->defaults('api_version', 'v3')
                    ->name('v3.vehicles.search');
                Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])
                    ->defaults('api_version', 'v3')
                    ->where('vehicle', '.*')
                    ->name('v3.vehicles.show');
            });

            Route::get('items', [ItemController::class, 'index'])->defaults('category', 'items')->name('items.index');
            Route::get('items/filters', [ItemController::class, 'filters'])->defaults('category', 'items')->name('items.filters');
            Route::post('items/search', [ItemController::class, 'search'])->name('items.search');
            Route::get('items/{identifier}', [ItemController::class, 'show'])->defaults('category', 'items')->where('identifier', '.*')->name('items.show');

            Route::get('weapons', [ItemController::class, 'index'])->defaults('category', 'weapons')->name('weapons.index');
            Route::get('weapons/{identifier}', [ItemController::class, 'show'])->defaults('category', 'weapons')->name('weapons.show');

            Route::get('weapon-attachments', [ItemController::class, 'index'])->defaults('category', 'weapon-attachments')->name('attachments.index');
            Route::get('weapon-attachments/{identifier}', [ItemController::class, 'show'])->defaults('category', 'weapon-attachments')->name('attachments.show');

            Route::get('clothes', [ItemController::class, 'index'])->defaults('category', 'clothes')->name('clothes.index');
            Route::get('clothes/{identifier}', [ItemController::class, 'show'])->defaults('category', 'clothes')->where('identifier', '.*')->name('clothes.show');

            Route::get('armor', [ItemController::class, 'index'])->defaults('category', 'armor')->name('armor.index');
            Route::get('armor/{identifier}', [ItemController::class, 'show'])->defaults('category', 'armor')->where('identifier', '.*')->name('armor.show');

            Route::get('food', [ItemController::class, 'index'])->defaults('category', 'food')->name('food.index');
            Route::get('food/{identifier}', [ItemController::class, 'show'])->defaults('category', 'food')->where('identifier', '.*')->name('food.show');

            Route::get('vehicle-weapons', [ItemController::class, 'index'])->defaults('category', 'vehicle-weapons')->name('sc.vehicles.index');
            Route::get('vehicle-weapons/{identifier}', [ItemController::class, 'show'])->defaults('category', 'vehicle-weapons')->name('sc.vehicles.show');

            Route::get('vehicle-items', [ItemController::class, 'index'])->defaults('category', 'vehicle-items')->name('vehicle-items.index');
            Route::get('vehicle-items/{identifier}', [ItemController::class, 'show'])->defaults('category', 'vehicle-items')->where('identifier', '.*')->name('vehicle-items.show');

            Route::get('locations', [StarmapLocationController::class, 'index'])->name('locations.index');
            Route::get('locations/filters', [StarmapLocationController::class, 'filters'])->name('locations.filters');
            Route::get('locations/{identifier}', [StarmapLocationController::class, 'show'])->name('locations.show');

            Route::get('manufacturers', [ManufacturerController::class, 'index'])->name('manufacturers.index');
            Route::post('manufacturers/search', [ManufacturerController::class, 'search'])->name('manufacturers.search');
            Route::get('manufacturers/{manufacturer}', [ManufacturerController::class, 'show'])->name('manufacturers.show');

            Route::get('resource-types', static fn (Request $request) => redirect()->route('commodities.index', $request->query(), 308));

            // Commodities
            Route::get('commodities', [CommodityController::class, 'index'])->name('commodities.index');
            Route::get('commodities/filters', [CommodityController::class, 'filters'])->name('commodities.filters');
            Route::get('commodities/{commodity}', [CommodityController::class, 'show'])->name('commodities.show');

            Route::get('blueprints', [BlueprintController::class, 'index'])->name('blueprints.index');
            Route::get('blueprints/filters', [BlueprintController::class, 'filters'])->name('blueprints.filters');
            Route::get('blueprints/{blueprint}', [BlueprintController::class, 'show'])->name('blueprints.show');

            // Factions
            Route::get('factions', [FactionController::class, 'index'])->name('factions.index');
            Route::get('factions/{faction}', [FactionController::class, 'show'])->whereUuid('faction')->name('factions.show');

            // Missions
            Route::get('missions', [MissionController::class, 'index'])->name('missions.index');
            Route::get('missions/filters', [MissionController::class, 'filters'])->name('missions.filters');
            Route::get('missions/{mission}', [MissionController::class, 'show'])->name('missions.show');

            Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
            Route::get('vehicles/filters', [VehicleController::class, 'filters'])->name('vehicles.filters');
            Route::post('vehicles/search', [VehicleController::class, 'search'])->name('vehicles.search');
            Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->where('vehicle', '.*')->name('vehicles.show');

            Route::get('ground-vehicles', [VehicleController::class, 'index'])
                ->defaults('vehicle_type', 'ground-vehicles')
                ->name('ground-vehicles.index');
            Route::post('ground-vehicles/search', [VehicleController::class, 'search'])
                ->defaults('vehicle_type', 'ground-vehicles')
                ->name('ground-vehicles.search');
            Route::get('ground-vehicles/{vehicle}', [VehicleController::class, 'show'])
                ->defaults('vehicle_type', 'ground-vehicles')
                ->where('vehicle', '.*')
                ->name('ground-vehicles.show');

            Route::get('gravlev-vehicles', [VehicleController::class, 'index'])
                ->defaults('vehicle_type', 'gravlev-vehicles')
                ->name('gravlev-vehicles.index');
            Route::post('gravlev-vehicles/search', [VehicleController::class, 'search'])
                ->defaults('vehicle_type', 'gravlev-vehicles')
                ->name('gravlev-vehicles.search');
            Route::get('gravlev-vehicles/{vehicle}', [VehicleController::class, 'show'])
                ->defaults('vehicle_type', 'gravlev-vehicles')
                ->where('vehicle', '.*')
                ->name('gravlev-vehicles.show');
        });

        // CommLink
        Route::get('comm-links', [CommLinkController::class, 'index'])->name('comm-links.index');
        Route::get('comm-links/filters', [CommLinkController::class, 'filters'])->name('comm-links.filters');
        Route::get('comm-links/{id}', [CommLinkController::class, 'show'])->name('comm-links.show');
        Route::post('comm-links/search', [CommLinkSearchController::class, 'searchByTitle'])->name('comm-links.search');
        Route::post('comm-links/reverse-image-link-search', [CommLinkSearchController::class, 'reverseImageLinkSearch'])->name('comm-links.reverse-link-search');
        Route::post('comm-links/reverse-image-search', [CommLinkSearchController::class, 'reverseImageSearch'])->name('comm-links.reverse-image-search');

        // CommLink Images
        Route::get('comm-link-images', [ImageController::class, 'index'])->name('comm-link-images.index');
        Route::get('comm-link-images/{image}', [ImageController::class, 'show'])->whereNumber('image')->name('comm-link-images.show');
        Route::get('comm-link-images/random', [ImageController::class, 'random'])->name('comm-link-images.random');
        Route::post('comm-link-images/search', [ImageController::class, 'search'])->name('comm-link-images.search');
        Route::get('comm-link-images/{image}/similar', [CommLinkSearchController::class, 'similarSearch'])->whereNumber('image')->middleware(['auth:sanctum', 'throttle:similar-image-search'])->name('comm-link-images.similar');

        // Galactapedia
        Route::get('galactapedia', [GalactapediaController::class, 'index'])->name('galactapedia.index');
        Route::get('galactapedia/filters', [GalactapediaController::class, 'filters'])->name('galactapedia.filters');
        Route::post('galactapedia/search', [GalactapediaController::class, 'search'])->name('galactapedia.search');
        Route::get('galactapedia/{article}', [GalactapediaController::class, 'show'])->name('galactapedia.show');

        // Stats
        Route::get('stats', [StatController::class, 'index'])->name('stats.index');
        Route::get('stats/latest', [StatController::class, 'latest'])->name('stats.latest');

        // Starmap
        Route::get('starsystems', [StarsystemController::class, 'index'])->name('starsystems.index');
        Route::get('starsystems/filters', [StarsystemController::class, 'filters'])->name('starsystems.filters');
        Route::post('starsystems/search', [StarsystemController::class, 'search'])->name('starsystems.search');
        Route::get('starsystems/{code}', [StarsystemController::class, 'show'])->name('starsystems.show');
        Route::get('celestial-objects', [CelestialObjectController::class, 'index'])->name('celestial-objects.index');
        Route::post('celestial-objects/search', [CelestialObjectController::class, 'search'])->name('celestial-objects.search');
        Route::get('celestial-objects/{code}', [CelestialObjectController::class, 'show'])->name('celestial-objects.show');

        Route::prefix('shipmatrix')->name('shipmatrix.')->group(function () {
            Route::prefix('vehicles')->name('vehicles.')->group(function () {
                Route::get('/', [ShipMatrixVehicleController::class, 'index'])->name('index');
                Route::get('/filters', [ShipMatrixVehicleController::class, 'filters'])->name('filters');
                Route::post('/search', [ShipMatrixVehicleController::class, 'search'])->name('search');
                Route::get('/{vehicle}', [ShipMatrixVehicleController::class, 'show'])
                    ->name('show')
                    ->where('vehicle', '.*');
            });
        });
    }
);

// Game Versions - OUTSIDE game.version middleware
Route::get('game-versions', [GameVersionController::class, 'index'])
    ->name('game-versions.index');
Route::get('game-versions/default', [GameVersionController::class, 'default'])
    ->name('game-versions.default');
Route::get('game-versions/{identifier}', [GameVersionController::class, 'show'])
    ->name('game-versions.show');
Route::get('game-versions/{version}/changelog', [VersionChangelogController::class, 'show'])
    ->name('game-versions.changelog');
Route::get('game-versions/{version}/changelog/changes', [VersionChangelogController::class, 'changes'])
    ->name('game-versions.changelog.changes');
