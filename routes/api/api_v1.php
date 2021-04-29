<?php

declare(strict_types=1);

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

$api->group(
    [
        'namespace' => 'StarCitizenUnpacked',
    ],
    static function (Router $api) {
        $api->group(
            [
                'namespace' => 'WeaponPersonal',
                'prefix' => 'weapons',
            ],
            static function (Router $api) {

                /**
                 * Index
                 */
                $api->get(
                    '/personal',
                    [
                        'as' => 'api.v1.scunpacked.weapons.personal.index',
                        'uses' => 'WeaponPersonalController@index',
                    ]
                );
                $api->get(
                    '/personal/{weapon}',
                    ['as' => 'api.v1.scunpacked.weapons.personal.show', 'uses' => 'WeaponPersonalController@show']
                );
            }
        );

        $api->group(
            [
                'prefix' => 'char',
            ],
            static function (Router $api) {
                $api->group(
                    [
                        'namespace' => 'CharArmor',
                    ],
                    static function (Router $api) {
                        /**
                         * Index
                         */
                        $api->get(
                            '/armor',
                            [
                                'as' => 'api.v1.scunpacked.char.armor.index',
                                'uses' => 'CharArmorController@index',
                            ]
                        );
                        $api->get(
                            '/armor/{armor}',
                            ['as' => 'api.v1.scunpacked.char.armor.show', 'uses' => 'CharArmorController@show']
                        );
                    }
                );

                $api->group(
                    [
                        'namespace' => 'Item',
                    ],
                    static function (Router $api) {
                        /**
                         * Index
                         */
                        $api->get(
                            '/clothing',
                            [
                                'as' => 'api.v1.scunpacked.char.clothing.index',
                                'uses' => 'ItemController@indexClothing',
                            ]
                        );
                        $api->get(
                            '/clothing/{name}',
                            ['as' => 'api.v1.scunpacked.char.clothing.show', 'uses' => 'ItemController@showClothing']
                        );
                    }
                );
            }
        );

        $api->group(
            [
                'namespace' => 'Shop',
                'prefix' => 'shops',
            ],
            static function (Router $api) {
                /**
                 * Index
                 */
                $api->get(
                    '/',
                    [
                        'as' => 'api.v1.scunpacked.shops.index',
                        'uses' => 'ShopController@index',
                    ]
                );

                $api->get(
                    '/position/{position}',
                    [
                        'as' => 'api.v1.scunpacked.shops.position.show',
                        'uses' => 'ShopController@showPosition',
                    ]
                );

                $api->get(
                    '/name/{name}',
                    [
                        'as' => 'api.v1.scunpacked.shops.name.show',
                        'uses' => 'ShopController@showName',
                    ]
                );

                $api->get(
                    '/{position}/{name}',
                    [
                        'as' => 'api.v1.scunpacked.shops.position.name.show',
                        'uses' => 'ShopController@showShopAtPosition',
                    ]
                );

                $api->get(
                    '/{shop}',
                    ['as' => 'api.v1.scunpacked.shops.show', 'uses' => 'ShopController@show']
                );
            }
        );

        $api->group(
            [
                'namespace' => 'Item',
                'prefix' => 'items',
            ],
            static function (Router $api) {
                /**
                 * Index
                 */
                $api->get(
                    '/',
                    [
                        'as' => 'api.v1.scunpacked.items.index',
                        'uses' => 'ItemController@index',
                    ]
                );

                $api->get(
                    '/tradeables',
                    [
                        'as' => 'api.v1.scunpacked.items.tradeables.index',
                        'uses' => 'ItemController@indexTradeables',
                    ]
                );

                $api->get(
                    '/{item}',
                    ['as' => 'api.v1.scunpacked.items.show', 'uses' => 'ItemController@show']
                );
            }
        );

        $api->group(
            [
                'namespace' => 'Ship',
                'prefix' => 'ship-items',
            ],
            static function (Router $api) {
                /**
                 * Cooler
                 */
                $api->group(
                    [
                        'prefix' => 'coolers',
                    ],
                    static function (Router $api) {
                        /**
                         * Index
                         */
                        $api->get(
                            '/',
                            [
                                'as' => 'api.v1.scunpacked.ship-items.coolers.index',
                                'uses' => 'CoolerController@index',
                            ]
                        );

                        $api->get(
                            '/{item}',
                            ['as' => 'api.v1.scunpacked.ship-items.coolers.show', 'uses' => 'CoolerController@show']
                        );
                    }
                );

                /**
                 * Power Plants
                 */
                $api->group(
                    [
                        'prefix' => 'power-plants',
                    ],
                    static function (Router $api) {
                        /**
                         * Index
                         */
                        $api->get(
                            '/',
                            [
                                'as' => 'api.v1.scunpacked.ship-items.power-plants.index',
                                'uses' => 'PowerPlantController@index',
                            ]
                        );

                        $api->get(
                            '/{item}',
                            [
                                'as' => 'api.v1.scunpacked.ship-items.power-plants.show',
                                'uses' => 'PowerPlantController@show'
                            ]
                        );
                    }
                );

                /**
                 * Quantum Drives
                 */
                $api->group(
                    [
                        'prefix' => 'quantum-drives',
                    ],
                    static function (Router $api) {
                        /**
                         * Index
                         */
                        $api->get(
                            '/',
                            [
                                'as' => 'api.v1.scunpacked.ship-items.quantum-drives.index',
                                'uses' => 'QuantumDriveController@index',
                            ]
                        );

                        $api->get(
                            '/{item}',
                            [
                                'as' => 'api.v1.scunpacked.ship-items.quantum-drives.show',
                                'uses' => 'QuantumDriveController@show'
                            ]
                        );
                    }
                );

                /**
                 * Shields
                 */
                $api->group(
                    [
                        'prefix' => 'shields',
                    ],
                    static function (Router $api) {
                        /**
                         * Index
                         */
                        $api->get(
                            '/',
                            [
                                'as' => 'api.v1.scunpacked.ship-items.shields.index',
                                'uses' => 'ShieldController@index',
                            ]
                        );

                        $api->get(
                            '/{item}',
                            [
                                'as' => 'api.v1.scunpacked.ship-items.shields.show',
                                'uses' => 'ShieldController@show'
                            ]
                        );
                    }
                );
            }
        );
    }
);
