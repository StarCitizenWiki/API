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

    echo "<table style='width:100%'>";
    echo "<tr>";
    echo "<td width='10%'><h4>HTTP Method</h4></td>";
    echo "<td width='10%'><h4>Route</h4></td>";
    echo "<td width='10%'><h4>Name</h4></td>";
    echo "<td width='70%'><h4>Corresponding Action</h4></td>";
    echo "</tr>";
    foreach ($routeCollection as $value) {
        echo "<tr>";
        echo "<td>" . $value->getMethods()[0] . "</td>";
        echo "<td>" . $value->getPath() . "</td>";
        echo "<td>" . $value->getName() . "</td>";
        echo "<td>" . $value->getActionName() . "</td>";
        echo "</tr>";
    }
    echo "</table>";
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
