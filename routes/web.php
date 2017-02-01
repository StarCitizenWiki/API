<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function() {
    $routeCollection = Route::getRoutes();

    dd($routeCollection);
});


Route::group(['namespace' => 'Tools'], function () {
    Route::group(['prefix' => 'tools'], function () {
        Route::get('imageresizer', ['uses' => 'ImageResizeController@index']);
    });

    Route::group(['prefix' => 'media', 'middleware' => ['api']], function () {
        Route::group(['prefix' => 'images'], function () {
            Route::get('funds', ['uses' => 'FundImageController@getImage', 'type' => FUNDIMAGE_FUNDING_ONLY]);
            Route::group(['prefix' => 'funds'], function () {
                Route::get('text', ['uses' => 'FundImageController@getImage', 'type' => FUNDIMAGE_FUNDING_AND_TEXT]);
                Route::get('bar', ['uses' => 'FundImageController@getImage', 'type' => FUNDIMAGE_FUNDING_AND_BARS]);
            });
        });

        Route::group(['prefix' => 'videos'], function () {

        });
    });
});
