<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['middleware' => ['api', 'throttle:60,1']], function () {

    Route::group(['prefix' => 'v1'], function () {

        Route::group(['namespace' => 'StarCitizen'], function () {

            Route::group(['prefix' => 'stats'], function () {
                Route::get('funds', ['uses' => 'StatsAPIController@getFunds']);
                Route::get('fleet', ['uses' => 'StatsAPIController@getFleet']);
                Route::get('fans', ['uses' => 'StatsAPIController@getFans']);
                Route::get('all', ['uses' => 'StatsAPIController@getAll']);
                Route::group(['prefix' => 'funds'], function () {
                    Route::get('lasthours', ['uses' => 'StatsAPIController@getLastHoursFunds']);
                    Route::get('lastdays', ['uses' => 'StatsAPIController@getLastDaysFunds']);
                    Route::get('lastweeks', ['uses' => 'StatsAPIController@getLastWeeksFunds']);
                    Route::get('lastmonth', ['uses' => 'StatsAPIController@getLastMonthsFunds']);
                });
            });

            Route::group(['prefix' => 'starmap'], function () {
                Route::post('search', function(){});
                Route::get('systems', function(){});

                Route::group(['prefix' => 'systems'], function () {
                    Route::get('{name}', function(){});
                    Route::get('{name}/asteroidbelts', function(){});
                    Route::get('{name}/spacestations', function(){});
                    Route::get('{name}/jumppoints', function(){});
                    Route::get('{name}/planets', function(){});
                    Route::get('{name}/moons', function(){});
                });

            });

            Route::group(['prefix' => 'community'], function () {
                Route::get('livestreamers', function(){});
			    Route::get('deepspaceradar', function(){});
			    Route::get('trackedposts', function(){});
            });

            Route::group(['prefix' => 'hubs'], function () {
                Route::post('search', function(){});
            });

            Route::group(['prefix' => 'orgs'], function () {
                Route::post('search', function(){});
            });

            Route::group(['prefix' => 'leaderboards'], function () {

            });

        });

        Route::group(['namespace' => 'Wiki'], function () {

            Route::group(['prefix' => 'ships'], function () {
                Route::post('search', function(){});
                Route::get('list', function(){});
                Route::get('{name}', function($name){ return $name; });
            });

            Route::group(['prefix' => 'weapons'], function () {
                Route::post('search', function(){});
                Route::get('list', function(){});
                Route::get('{name}', function($name){ return $name; });
            });
        });
    });
});
