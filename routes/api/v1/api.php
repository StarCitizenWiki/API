<?php declare(strict_types = 1);
Route::group(
    ['namespace' => 'StarCitizen'],
    function () {

        Route::group(
            ['prefix' => 'stats'],
            function () {
                Route::get('funds', ['uses' => 'StatsApiController@getFunds']);
                Route::get('fleet', ['uses' => 'StatsApiController@getFleet']);
                Route::get('fans', ['uses' => 'StatsApiController@getFans']);
                Route::get('all', ['uses' => 'StatsApiController@getAll']);
                Route::group(
                    ['prefix' => 'funds'],
                    function () {
                        Route::get('lasthours', ['uses' => 'StatsApiController@getLastHoursFunds']);
                        Route::get('lastdays', ['uses' => 'StatsApiController@getLastDaysFunds']);
                        Route::get('lastweeks', ['uses' => 'StatsApiController@getLastWeeksFunds']);
                        Route::get('lastmonth', ['uses' => 'StatsApiController@getLastMonthsFunds']);
                    }
                );
            }
        );

        Route::group(
            ['prefix' => 'starmap'],
            function () {
                Route::get('search/{searchstring}', ['uses' => 'StarmapApiController@searchStarmap']);
                Route::get('systems', ['uses' => 'StarmapApiController@getSystemList']);

                Route::group(
                    ['prefix' => 'systems'],
                    function () {
                        Route::get('{name}', ['uses' => 'StarmapApiController@getSystem']);
                        Route::get('{name}/asteroidbelts', ['uses' => 'StarmapApiController@getAsteroidbelts']);
                        Route::get('{name}/spacestations', ['uses' => 'StarmapApiController@getSpacestations']);
                        Route::get('{name}/jumppoints', ['uses' => 'StarmapApiController@getJumppoints']);
                        Route::get('{name}/planets', ['uses' => 'StarmapApiController@getPlanets']);
                        Route::get('{name}/moons', ['uses' => 'StarmapApiController@getMoons']);
                        Route::get('{name}/stars', ['uses' => 'StarmapApiController@getStars']);
                        Route::get('{name}/landingzones', ['uses' => 'StarmapApiController@getLandingzones']);
                    }
                );

                Route::get('tunnels', ['uses' => 'JumppointTunnelAPIController@getJumppointtunnels']);

                Route::group(
                    ['prefix' => 'systems'],
                    function () {
                        Route::get('id/{cig_id}', ['uses' => 'JumppointTunnelAPIController@getJumppointTunnelById']);
                        Route::get(
                            'system/{name}',
                            ['uses' => 'JumppointTunnelAPIController@getJumppointTunnelBySystem']
                        );
                    }
                );

                Route::group(
                    ['prefix' => 'objects'],
                    function () {
                        Route::get('{objectname}', ['uses' => 'StarmapAPIController@getObjectList']);
                    }
                );
            }
        );

        // Alle Astroidbelts, Spaceestations, etc. fuer gesamtes Universum holen

        Route::group(
            ['prefix' => 'community'],
            function () {
                Route::get(
                    'livestreamers',
                    function () {
                    }
                );
                Route::get(
                    'deepspaceradar',
                    function () {
                    }
                );
                Route::get(
                    'trackedposts',
                    function () {
                    }
                );
            }
        );

        Route::group(
            ['prefix' => 'hubs'],
            function () {
                Route::post(
                    'search',
                    function () {
                    }
                );
            }
        );

        Route::group(
            ['prefix' => 'orgs'],
            function () {
                Route::post(
                    'search',
                    function () {
                    }
                );
            }
        );

        Route::group(
            ['prefix' => 'leaderboards'],
            function () {
            }
        );
    }
);

Route::group(
    ['prefix' => 'ships'],
    function () {
        Route::group(
            ['namespace' => 'StarCitizenWiki'],
            function () {
                Route::post('search', ['uses' => 'ShipsApiController@searchShips']);
                Route::get('list', ['uses' => 'ShipsApiController@getShipList']);
                Route::get('{name}', ['uses' => 'ShipsApiController@getShip']);
            }
        );
        Route::group(
            ['namespace' => 'StarCitizenDB'],
            function () {
                Route::group(
                    ['prefix' => 'scdb'],
                    function () {
                        Route::post('search', ['uses' => 'ShipsApiController@searchShips']);
                        Route::get('list', ['uses' => 'ShipsApiController@getShipList']);
                    }
                );
            }
        );
    }
);

Route::group(
    ['namespace' => 'StarCitizenWiki', 'prefix' => 'weapons'],
    function () {
        Route::post(
            'search',
            function () {
            }
        );
        Route::get(
            'list',
            function () {
            }
        );
        Route::get(
            '{name}',
            function ($name) {
                return $name;
            }
        );
    }
);
