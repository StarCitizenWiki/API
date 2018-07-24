<?php declare(strict_types = 1);

Route::namespace('StarCitizen')->group(
    function () {
        Route::prefix('stats')
            ->namespace('Stat')
            ->group(
                function () {
                    Route::get('funds', 'StatsController@getFunds');
                    Route::get('fleet', 'StatsApiController@getFleet');
                    Route::get('fans', 'StatsApiController@getFans');
                    Route::get('all', 'StatController@getAll');

                    Route::prefix('funds')->group(
                        function () {
                            Route::get('lasthours', 'StatsApiController@getLastHoursFunds');
                            Route::get('lastdays', 'StatsApiController@getLastDaysFunds');
                            Route::get('lastweeks', 'StatsApiController@getLastWeeksFunds');
                            Route::get('lastmonth', 'StatsApiController@getLastMonthsFunds');
                        }
                    );
                }
            );

        Route::prefix('starmap')
            ->namespace('Starmap')
            ->group(
                function () {
                    Route::get('search/{searchstring}', 'StarmapController@searchStarmap');
                    Route::get('systems', 'StarmapController@getSystemList');

                    Route::prefix('systems')->group(
                        function () {
                            Route::get('{name}', 'StarmapController@getSystem');
                            Route::get('{name}/asteroidbelts', 'StarmapController@getAsteroidbelts');
                            Route::get('{name}/spacestations', 'StarmapController@getSpacestations');
                            Route::get('{name}/jumppoints', 'StarmapController@getJumppoints');
                            Route::get('{name}/planets', 'StarmapController@getPlanets');
                            Route::get('{name}/moons', 'StarmapController@getMoons');
                            Route::get('{name}/stars', 'StarmapController@getStars');
                            Route::get('{name}/landingzones', 'StarmapController@getLandingzones');
                        }
                    );

                    Route::get('tunnels', 'JumppointTunnelController@getJumppointtunnels');

                    Route::prefix('systems')->group(
                        function () {
                            Route::get(
                                'id/{cig_id}',
                                'JumppointTunnelController@getJumppointTunnelById'
                            );
                            Route::get(
                                'system/{name}',
                                'JumppointTunnelController@getJumppointTunnelBySystem'
                            );
                        }
                    );

                    Route::prefix('objects')->group(
                        function () {
                            Route::get('{objectname}', 'StarmapController@getObjectList');
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
/*
Route::namespace('StarCitizenWiki')->group(
    function () {
        Route::prefix('ships')->group(
            function () {
                Route::post('search', 'ShipsController@searchShips');
                Route::get('list', 'ShipsController@getShipList');
                Route::get('{name}', 'ShipsController@getShip');
            }
        );

        Route::prefix('weapons')->group(
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
    }
);*/
