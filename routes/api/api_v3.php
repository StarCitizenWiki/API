<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::group(
    [
        'namespace' => 'SC',
    ],
    static function () {
        Route::group([
            'namespace' => 'Vehicle',
        ], static function () {
            Route::get('vehicles', 'VehicleController@index')->name('vehicles.index');
            Route::post('vehicles/search', 'VehicleController@search')->name('vehicles.search');
            Route::get('vehicles/{vehicle}', 'VehicleController@show')->name('vehicles.show');
        });
    }
);
