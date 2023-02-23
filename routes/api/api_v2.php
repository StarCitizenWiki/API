<?php

declare(strict_types=1);

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
