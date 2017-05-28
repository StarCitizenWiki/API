<?php
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
        Route::get('search/{searchstring}', ['uses' => 'StarmapAPIController@searchStarmap']);
        Route::get('systems', ['uses' => 'StarmapAPIController@getSystemList']);

        Route::group(['prefix' => 'systems'], function () {
            Route::get('{name}', ['uses' => 'StarmapAPIController@getSystem']);
            Route::get('{name}/asteroidbelts', ['uses' => 'StarmapAPIController@getAsteroidbelts']);
            Route::get('{name}/spacestations', ['uses' => 'StarmapAPIController@getSpacestations']);
            Route::get('{name}/jumppoints', ['uses' => 'StarmapAPIController@getJumppoints']);
            Route::get('{name}/planets', ['uses' => 'StarmapAPIController@getPlanets']);
            Route::get('{name}/moons', ['uses' => 'StarmapAPIController@getMoons']);
            Route::get('{name}/stars', ['uses' => 'StarmapAPIController@getStars']);
            Route::get('{name}/landingzones', ['uses' => 'StarmapAPIController@getLandingzones']);
        });

        Route::group(['prefix' => 'objects'], function () {
            Route::get('{objectname}', ['uses' => 'StarmapAPIController@getObject']);
        });
    });

    // Alle Astroidbelts, Spaceestations, etc. fuer gesamtes Universum holen

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

Route::group(['prefix' => 'ships'], function () {
    Route::group(['namespace' => 'StarCitizenWiki'], function () {
        Route::post('search', ['uses' => 'ShipsAPIController@searchShips']);
        Route::get('list', ['uses' => 'ShipsAPIController@getShipList']);
        Route::get('{name}', ['uses' => 'ShipsAPIController@getShip']);
    });
    Route::group(['namespace' => 'StarCitizenDB'], function () {
        Route::group(['prefix' => 'scdb'], function () {
            Route::post('search', ['uses' => 'ShipsAPIController@searchShips']);
            Route::get('list', ['uses' => 'ShipsAPIController@getShipList']);
        });
    });
});

Route::group(['namespace' => 'StarCitizenWiki', 'prefix' => 'weapons'], function () {
    Route::post('search', function(){});
    Route::get('list', function(){});
    Route::get('{name}', function($name){ return $name; });
});
