<?php

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
if (App::isLocal() || App::runningUnitTests()) {
    $api_domain = env('APP_URL');
    $tools_domain = env('APP_URL');
} else {
    $api_domain = API_DOMAIN;
    $tools_domain = TOOLS_DOMAIN;
}

Route::group(['domain' => $api_domain], function () {
    Route::group(['middleware' => ['api', 'throttle:60,1']], function () {

        Route::group(['prefix' => 'v1'], function () {

            Route::group(['namespace' => 'StarCitizen'], function () {

                Route::group(['prefix' => 'stats'], function () {
                    Route::get('funds', ['uses' => 'StatsAPIController@getFunds']);
                    Route::get('fleet', ['uses' => 'StatsAPIController@getFleet']);
                    Route::get('fans', ['uses' => 'StatsAPIController@getFans']);
                    Route::get('json', ['uses' => 'StatsAPIController@getStatsAsJSON']);
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

            Route::group(['namespace' => 'StarCitizenWiki'], function () {

                Route::group(['prefix' => 'ships'], function () {
                    Route::post('search', function(){});
                    Route::get('list', ['uses' => 'ShipsAPIController@getShipList']);
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
});

