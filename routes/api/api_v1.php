<?php declare(strict_types = 1);

use Dingo\Api\Routing\Router;

$api->group(
    [
        'namespace' => 'StarCitizen',
    ],
    static function (Router $api) {
        $api->group(
            [
                'namespace' => 'Stat',
                'prefix' => 'stats',
            ],
            static function (Router $api) {
                $api->get('latest', ['as' => 'api.v1.starcitizen.stats.latest', 'uses' => 'StatController@latest']);
                $api->get('/', ['as' => 'api.v1.starcitizen.stats.all', 'uses' => 'StatController@index']);
            }
        );

        $api->group(
            [
                'namespace' => 'Manufacturer',
                'prefix' => 'manufacturers',
            ],
            static function (Router $api) {
                $api->get(
                    '/',
                    ['as' => 'api.v1.starcitizen.manufacturers.all', 'uses' => 'ManufacturerController@index']
                );
                $api->get(
                    '{manufacturer}',
                    ['as' => 'api.v1.starcitizen.manufacturers.show', 'uses' => 'ManufacturerController@show']
                );
                $api->post(
                    '/search',
                    ['as' => 'api.v1.starcitizen.manufacturers.search', 'uses' => 'ManufacturerController@search']
                );
            }
        );

        $api->group(
            [
                'namespace' => 'Vehicle',
            ],
            static function (Router $api) {
                $api->group(
                    [
                        'namespace' => 'Ship',
                        'prefix' => 'ships',
                    ],
                    static function (Router $api) {
                        $api->get(
                            '/',
                            ['as' => 'api.v1.starcitizen.vehicles.ships.all', 'uses' => 'ShipController@index']
                        );
                        $api->get(
                            '{ship}',
                            ['as' => 'api.v1.starcitizen.vehicles.ships.show', 'uses' => 'ShipController@show']
                        );
                        $api->post(
                            '/search',
                            ['as' => 'api.v1.starcitizen.vehicles.ships.search', 'uses' => 'ShipController@search']
                        );
                    }
                );

                $api->group(
                    [
                        'namespace' => 'GroundVehicle',
                        'prefix' => 'vehicles',
                    ],
                    static function (Router $api) {
                        $api->get(
                            '/',
                            [
                                'as' => 'api.v1.starcitizen.vehicles.ground-vehicles.all',
                                'uses' => 'GroundVehicleController@index',
                            ]
                        );
                        $api->get(
                            '{ground_vehicle}',
                            [
                                'as' => 'api.v1.starcitizen.vehicles.ground-vehicles.show',
                                'uses' => 'GroundVehicleController@show',
                            ]
                        );
                        $api->post(
                            '/search',
                            [
                                'as' => 'api.v1.starcitizen.vehicles.ground-vehicles.search',
                                'uses' => 'GroundVehicleController@search',
                            ]
                        );
                    }
                );
            }
        );

        $api->group(
            [
                'namespace' => 'Starmap',
                'prefix' => 'starmap',
            ],
            static function (Router $api) {
                $api->group(
                    [
                        'namespace' => 'Starsystem',
                        'prefix' => 'starsystems',
                    ],
                    function (Router $api) {
                        $api->get(
                            '/',
                            ['as' => 'api.v1.starmap.starsystems.index', 'uses' => 'StarsystemController@index']
                        );
                        $api->get(
                            '{code}',
                            ['as' => 'api.v1.starmap.starsystems.show', 'uses' => 'StarsystemController@show']
                        );
                    }
                );

                $api->group(
                    [
                        'namespace' => 'CelestialObject',
                        'prefix' => 'celestial-objects',
                    ],
                    static function (Router $api) {
                        $api->get(
                            '/',
                            [
                                'as' => 'api.v1.starmap.celestial-objects.index',
                                'uses' => 'CelestialObjectController@index',
                            ]
                        );
                        $api->get(
                            '{code}',
                            [
                                'as' => 'api.v1.starmap.celestial-objects.show',
                                'uses' => 'CelestialObjectController@show',
                            ]
                        );
                    }
                );
            }
        );

        $api->group(
            [
                'namespace' => 'Galactapedia',
                'prefix' => 'galactapedia',
            ],
            static function (Router $api) {
                $api->get(
                    '/',
                    ['as' => 'api.v1.starcitizen.galactapedia.all', 'uses' => 'GalactapediaController@index']
                );
                $api->get(
                    '{article}',
                    ['as' => 'api.v1.starcitizen.galactapedia.show', 'uses' => 'GalactapediaController@show']
                );
                $api->post(
                    '/search',
                    ['as' => 'api.v1.starcitizen.galactapedia.search', 'uses' => 'GalactapediaController@search']
                );
            }
        );
    }
);

$api->group(
    [
        'namespace' => 'Rsi',
    ],
    static function (Router $api) {
        $api->group(
            [
                'namespace' => 'CommLink',
                'prefix' => 'comm-links',
            ],
            static function (Router $api) {
                /**
                 * Categories
                 */
                $api->group(
                    [
                        'namespace' => 'Category',
                        'prefix' => 'categories',
                    ],
                    static function (Router $api) {
                        $api->get(
                            '/',
                            [
                                'as' => 'api.v1.rsi.comm-links.categories.index',
                                'uses' => 'CategoryController@index',
                            ]
                        );
                        $api->get(
                            '{category}',
                            [
                                'as' => 'api.v1.rsi.comm-links.categories.show',
                                'uses' => 'CategoryController@show',
                            ]
                        );
                    }
                );

                /**
                 * Channels
                 */
                $api->group(
                    [
                        'namespace' => 'Channel',
                        'prefix' => 'channels',
                    ],
                    static function (Router $api) {
                        $api->get(
                            '/',
                            [
                                'as' => 'api.v1.rsi.comm-links.channels.index',
                                'uses' => 'ChannelController@index',
                            ]
                        );
                        $api->get(
                            '{channel}',
                            [
                                'as' => 'api.v1.rsi.comm-links.channels.show',
                                'uses' => 'ChannelController@show',
                            ]
                        );
                    }
                );

                /**
                 * Series
                 */
                $api->group(
                    [
                        'namespace' => 'Series',
                        'prefix' => 'series',
                    ],
                    static function (Router $api) {
                        $api->get(
                            '/',
                            [
                                'as' => 'api.v1.rsi.comm-links.series.index',
                                'uses' => 'SeriesController@index',
                            ]
                        );
                        $api->get(
                            '{series}',
                            [
                                'as' => 'api.v1.rsi.comm-links.series.show',
                                'uses' => 'SeriesController@show',
                            ]
                        );
                    }
                );

                /**
                 * Comm Links
                 */
                $api->post(
                    '/reverse-image-link-search',
                    [
                        'as' => 'api.v1.rsi.comm-links.reverse-image-link-search',
                        'uses' => 'CommLinkSearchController@reverseImageLinkSearch',
                    ]
                );
                $api->post(
                    '/reverse-image-search',
                    [
                        'as' => 'api.v1.rsi.comm-links.reverse-image-search',
                        'uses' => 'CommLinkSearchController@reverseImageSearch',
                    ]
                );
                $api->post(
                    '/search',
                    [
                        'as' => 'api.v1.rsi.comm-links.search',
                        'uses' => 'CommLinkSearchController@searchByTitle',
                    ]
                );
                $api->get(
                    '/',
                    [
                        'as' => 'api.v1.rsi.comm-links.index',
                        'uses' => 'CommLinkController@index',
                    ]
                );
                $api->get(
                    '{comm_link}',
                    [
                        'as' => 'api.v1.rsi.comm-links.show',
                        'uses' => 'CommLinkController@show',
                    ]
                );
            }
        );
    }
);

