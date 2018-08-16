<?php declare(strict_types = 1);

$api->group(
    [
        'namespace' => 'StarCitizen',
    ],
    function ($api) {
        $api->group(
            [
                'namespace' => 'Stat',
                'prefix' => 'stats',
            ],
            function ($api) {
                $api->get('latest', ['as' => 'api.v1.stats.latest', 'uses' => 'StatController@latest']);
                $api->get('/', ['as' => 'api.v1.stats.all', 'uses' => 'StatController@index']);
            }
        );

        $api->group(
            [
                'namespace' => 'Vehicle',
                'prefix' => 'vehicles',
            ],
            function ($api) {
                $api->group(
                    [
                        'namespace' => 'Ship',
                        'prefix' => 'ships',
                    ],
                    function ($api) {
                        $api->get('/', ['as' => 'api.v1.vehicles.ships.all', 'uses' => 'ShipController@index']);
                        $api->get('{ship}', ['as' => 'api.v1.vehicles.ships.show', 'uses' => 'ShipController@show']);
                    }
                );
            }
        );

        $api->group(
            [
                'namespace' => 'Starmap',
                'prefix' => 'starmap',
            ],
            function ($api) {
                $api->get('/', ['as' => 'api.v1.starsystem.all', 'uses' => 'StarsystemController@index']);
                $api->get('{code}', ['as' => 'api.v1.starsystem.show', 'uses' => 'StarsystemController@show']);
            },
            function ($api) {
                $api->get('/', ['as' => 'api.v1.jumppoint.all', 'uses' => 'JumppointController@index']);
                $api->get('{code}', ['as' => 'api.v1.jumppoint.show', 'uses' => 'JumppointController@show']);
            },
            function ($api) {
                $api->get('/', ['as' => 'api.v1.celestialobject.all', 'uses' => 'CelestialObjectController@index']);
                $api->get('{code}', ['as' => 'api.v1.celestialobject.show', 'uses' => 'CelestialObjectController@show']);
            }
        );

        /*
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
                );*/
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
