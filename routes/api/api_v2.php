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
    ],
    static function () {
        Route::get('comm-links', 'CommLinkController@index')->name('cl.index');
        Route::get('comm-links/{id}', 'CommLinkController@show')->name('cl.show');

        Route::post('comm-links/search', 'CommLinkSearchController@searchByTitle')->name('cl.search');
        Route::post('comm-links/reverse-image-link-search', 'CommLinkSearchController@reverseImageLinkSearch')->name('cl.search-link');
        Route::post('comm-links/reverse-image-search', 'CommLinkSearchController@reverseImageSearch')->name('cl.search-image');

        Route::get('comm-link-images', 'ImageController@index')->name('cli.index');
        Route::get('comm-link-images/random', 'ImageController@random')->name('cli.random');
        Route::post('comm-link-images/search', 'ImageController@search')->name('cli.search');
        #Route::get('comm-link-images/{image}', 'ImageController@show')->name('cli.show');
        Route::get('comm-link-images/{image}/similar', 'CommLinkSearchController@similarSearch')->name('cli.similar');
    }
);


Route::group(
    [
        'namespace' => 'StarCitizen',
    ],
    static function () {

        Route::get('stats', 'StatController@index')->name('stats.index');
        Route::get('stats/latest', 'StatController@latest')->name('stats.latest');

        Route::get('galactapedia', 'GalactapediaController@index')->name('galactapedia.index');
        Route::post('galactapedia/search', 'GalactapediaController@search')->name('galactapedia.search');
        Route::get('galactapedia/{article}', 'GalactapediaController@show')->name('galactapedia.show');

        Route::group(
            [
                'namespace' => 'Starmap',
                'name' => 'starmap'
            ],
            static function () {
                Route::get('starsystems', 'StarsystemController@index')->name('starsystem.index');
                Route::get('starsystems/{code}', 'StarsystemController@show')->name('starsystem.show');

                Route::get('celestial-objects', 'CelestialObjectController@index')->name('co.index');
                Route::get('celestial-objects/{code}', 'CelestialObjectController@show')->name('co.show');
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
                Route::get('weapons', 'PersonalWeaponController@index')->name('weapons.index');
                Route::get('weapons/{weapon}', 'PersonalWeaponController@show')->name('weapons.show');

                Route::get('weapon-attachments', 'WeaponAttachmentController@index')->name('attachments.index');
                Route::get('weapon-attachments/{attachment}', 'WeaponAttachmentController@show')->name('attachments.show');
            });

            Route::get('clothes', 'ClothesController@index')->name('clothes.index');
            Route::get('clothes/{clothing}', 'ClothesController@show')->name('clothes.show')
                ->where('clothing', '.*');

            Route::get('armor', 'ArmorController@index')->name('armor.index');
            Route::get('armor/{clothing}', 'ArmorController@show')->name('armor.show')
                ->where('clothing', '.*');
        });

        Route::group([
            'namespace' => 'Vehicle',
        ], static function () {
            Route::get('vehicles', 'VehicleController@index')->name('vehicles.index');
            Route::post('vehicles/search', 'VehicleController@search')->name('vehicles.search');
            Route::get('vehicles/{vehicle}', 'VehicleController@show')->name('vehicles.show');

            Route::get('vehicle-weapons', 'VehicleWeaponController@index')->name('sc.vehicles.index');
            Route::get('vehicle-weapons/{weapon}', 'VehicleWeaponController@show')->name('sc.vehicles.show');

            Route::get('vehicle-items', 'VehicleItemController@index')->name('vehicle-items.index');
            Route::get('vehicle-items/{item}', 'VehicleItemController@show')->name('vehicle-items.show')
                ->where('item', '.*');
        });

        Route::get('manufacturers', 'ManufacturerController@index')->name('manufacturers.index');
        Route::post('manufacturers/search', 'ManufacturerController@search')->name('manufacturers.search');
        Route::get('manufacturers/{manufacturer}', 'ManufacturerController@show')->name('manufacturers.show');

        Route::get('items', 'ItemController@index')->name('items.index');
        Route::post('items/search', 'ItemController@search')->name('items.search');
        Route::get('items/{item}', 'ItemController@show')->name('items.show')
            ->where('item', '.*');

        Route::get('shops', 'ShopController@index')->name('shops.index');
        Route::get('shops/{shop}', 'ShopController@show')->name('shops.show');

        Route::get('food', 'FoodController@index')->name('food.index');
        Route::get('food/{food}', 'FoodController@show')->name('food.show')
            ->where('food', '.*');
    }
);
