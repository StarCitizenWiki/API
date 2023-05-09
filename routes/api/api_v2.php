<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

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
        Route::group(
            [
                'namespace' => 'Stat',
                'name' => 'stat'
            ],
            static function () {
                Route::get('stats', 'StatController@index')->name('index');
                Route::get('stats/latest', 'StatController@latest')->name('latest');
            }
        );

        Route::group(
            [
                'namespace' => 'Vehicle',
                'name' => 'vehicle'
            ],
            static function () {
                Route::get('vehicles', 'VehicleController@index')->name('index');
                Route::get('vehicles/{vehicle}', 'VehicleController@show')->name('show');
            }
        );

        Route::group(
            [
                'namespace' => 'Galactapedia',
                'name' => 'galactapedia'
            ],
            static function () {
                Route::get('galactapedia', 'GalactapediaController@index')->name('index');
                Route::get('galactapedia/{article}', 'GalactapediaController@show')->name('show');
                Route::post('galactapedia/search', 'GalactapediaController@search')->name('search');
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
            Route::get('personal-weapons', 'PersonalWeaponController@index')->name('index');
            Route::get('personal-weapons/{weapon}', 'PersonalWeaponController@show')->name('show');

            Route::get('clothes', 'ClothesController@index')->name('index');
            Route::get('clothes/{clothing}', 'ClothesController@show')->name('show');

            Route::get('armor', 'ArmorController@index')->name('index');
            Route::get('armor/{clothing}', 'ArmorController@show')->name('show');
        });

        Route::get('manufacturers', 'ManufacturerController@index')->name('index');
        Route::get('manufacturers/{manufacturer}', 'ManufacturerController@show')->name('show');
        Route::post('manufacturers/search', 'ManufacturerController@search')->name('search');

        Route::get('items', 'ItemController@index')->name('index');
        Route::get('items/{item}', 'ItemController@show')->name('show');
        Route::post('items/search', 'ItemController@search')->name('search');

        Route::get('shops', 'ShopController@index')->name('index');
        Route::get('shops/{shop}', 'ShopController@show')->name('show');

        Route::get('food', 'FoodController@index')->name('index');
        Route::get('food/{food}', 'FoodController@show')->name('show');

        Route::get('vehicle-weapons', 'VehicleWeaponController@index')->name('index');
        Route::get('vehicle-weapons/{weapon}', 'VehicleWeaponController@show')->name('show');
    }
);
