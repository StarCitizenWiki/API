<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('openapi', static function () {
    $openapi = \OpenApi\Generator::scan([
        app_path('Http/Controllers/Api/V2'),
        app_path('Http/Resources'),
    ]);

    return response($openapi->toJson())->header('Content-Type', 'application/json');
});

Route::group(
    [
        'namespace' => 'Rsi\CommLink',
        'name' => 'comm-links'
    ],
    static function () {
        Route::get('comm-links', 'CommLinkController@index')->name('index');
        Route::get('comm-links/{id}', 'CommLinkController@show')->name('show');

        Route::post('comm-links/search', 'CommLinkSearchController@searchByTitle')->name('search');
        Route::post('comm-links/reverse-image-link-search', 'CommLinkSearchController@reverseImageLinkSearch')->name('search-link');
        Route::post('comm-links/reverse-image-search', 'CommLinkSearchController@reverseImageSearch')->name('search-image');
    }
);


Route::group(
    [
        'namespace' => 'StarCitizen',
    ],
    static function () {

        Route::get('stats', 'StatController@index')->name('index');
        Route::get('stats/latest', 'StatController@latest')->name('latest');

        Route::get('vehicles', 'VehicleController@index')->name('index');
        Route::post('vehicles/search', 'VehicleController@search')->name('search');
        Route::get('vehicles/{vehicle}', 'VehicleController@show')->name('show');

        Route::get('galactapedia', 'GalactapediaController@index')->name('index');
        Route::post('galactapedia/search', 'GalactapediaController@search')->name('search');
        Route::get('galactapedia/{article}', 'GalactapediaController@show')->name('show');

        Route::group(
            [
                'namespace' => 'Starmap',
                'name' => 'starmap'
            ],
            static function () {
                Route::get('starsystems', 'StarsystemController@index')->name('index');
                Route::get('starsystems/{code}', 'StarsystemController@show')->name('show');

                Route::get('celestial-objects', 'CelestialObjectController@index')->name('index');
                Route::get('celestial-objects/{code}', 'CelestialObjectController@show')->name('show');
            }
        );
    }
);

Route::group(
    [
        'namespace' => 'SC',
    ],
    static function () {
        Route::group([
            'namespace' => 'Char',
        ], static function () {
            Route::group([
                'namespace' => 'PersonalWeapon',
            ], static function () {
                Route::get('weapons', 'PersonalWeaponController@index')->name('index');
                Route::get('weapons/{weapon}', 'PersonalWeaponController@show')->name('show');

                Route::get('weapon-attachments', 'WeaponAttachmentController@index')->name('index');
                Route::get('weapon-attachments/{attachment}', 'WeaponAttachmentController@show')->name('show');
            });

            Route::get('clothes', 'ClothesController@index')->name('index');
            Route::get('clothes/{clothing}', 'ClothesController@show')->name('show')
                ->where('clothing', '.*');

            Route::get('armor', 'ArmorController@index')->name('index');
            Route::get('armor/{clothing}', 'ArmorController@show')->name('show')
                ->where('clothing', '.*');
        });

        Route::group([
            'namespace' => 'Vehicle',
        ], static function () {
            Route::get('vehicle-weapons', 'VehicleWeaponController@index')->name('index');
            Route::get('vehicle-weapons/{weapon}', 'VehicleWeaponController@show')->name('show');

            Route::get('vehicle-items', 'VehicleItemController@index')->name('index');
            Route::get('vehicle-items/{item}', 'VehicleItemController@show')->name('show')
                ->where('item', '.*');
        });

        Route::get('manufacturers', 'ManufacturerController@index')->name('index');
        Route::post('manufacturers/search', 'ManufacturerController@search')->name('search');
        Route::get('manufacturers/{manufacturer}', 'ManufacturerController@show')->name('show');

        Route::get('items', 'ItemController@index')->name('index');
        Route::post('items/search', 'ItemController@search')->name('search');
        Route::get('items/{item}', 'ItemController@show')->name('show')
            ->where('item', '.*');

        Route::get('shops', 'ShopController@index')->name('index');
        Route::get('shops/{shop}', 'ShopController@show')->name('show');

        Route::get('food', 'FoodController@index')->name('index');
        Route::get('food/{food}', 'FoodController@show')->name('show')
            ->where('food', '.*');
    }
);