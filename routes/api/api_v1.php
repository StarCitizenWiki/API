<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/openapi', function () {
    return response(
        \Illuminate\Support\Facades\File::get(storage_path('app/swagger.json'))
    )->header('Content-Type', 'application/json');
});

Route::group(
    [
        'namespace' => 'StarCitizen',
    ],
    static function () {
        Route::group(
            [
                'namespace' => 'Stat',
                'prefix' => 'stats',
            ],
            static function () {
                Route::get('latest', ['as' => 'api.v1.starcitizen.stats.latest', 'uses' => 'StatController@latest']);
                Route::get('/', ['as' => 'api.v1.starcitizen.stats.all', 'uses' => 'StatController@index']);
            }
        );

        Route::group(
            [
                'namespace' => 'Manufacturer',
                'prefix' => 'manufacturers',
            ],
            static function () {
                Route::get(
                    '/',
                    ['as' => 'api.v1.starcitizen.manufacturers.all', 'uses' => 'ManufacturerController@index']
                );
                Route::get(
                    '{manufacturer}',
                    ['as' => 'api.v1.starcitizen.manufacturers.show', 'uses' => 'ManufacturerController@show']
                );
                Route::post(
                    '/search',
                    ['as' => 'api.v1.starcitizen.manufacturers.search', 'uses' => 'ManufacturerController@search']
                );
            }
        );

        Route::group(
            [
                'namespace' => 'Vehicle',
            ],
            static function () {
                Route::group(
                    [
                        'prefix' => 'ships',
                    ],
                    static function () {
                        Route::get(
                            '/',
                            ['as' => 'api.v1.starcitizen.vehicles.ships.all', 'uses' => 'VehicleController@index']
                        );
                        Route::get(
                            '{vehicle}',
                            ['as' => 'api.v1.starcitizen.vehicles.ships.show', 'uses' => 'VehicleController@show']
                        );
                        Route::post(
                            '/search',
                            ['as' => 'api.v1.starcitizen.vehicles.ships.search', 'uses' => 'VehicleController@search']
                        );
                    }
                );

                Route::group(
                    [
                        'prefix' => 'vehicles',
                    ],
                    static function () {
                        Route::get(
                            '/',
                            ['as' => 'api.v1.starcitizen.vehicles.all', 'uses' => 'VehicleController@index']
                        );
                        Route::get(
                            '{vehicle}',
                            ['as' => 'api.v1.starcitizen.vehicles.show', 'uses' => 'VehicleController@show']
                        );
                        Route::post(
                            '/search',
                            ['as' => 'api.v1.starcitizen.vehicles.search', 'uses' => 'VehicleController@search']
                        );
                    }
                );
            }
        );

        Route::group(
            [
                'namespace' => 'Starmap',
                'prefix' => 'starmap',
            ],
            static function () {
                Route::group(
                    [
                        'namespace' => 'Starsystem',
                        'prefix' => 'starsystems',
                    ],
                    function () {
                        Route::get(
                            '/',
                            ['as' => 'api.v1.starmap.starsystems.index', 'uses' => 'StarsystemController@index']
                        );
                        Route::get(
                            '{code}',
                            ['as' => 'api.v1.starmap.starsystems.show', 'uses' => 'StarsystemController@show']
                        );
                    }
                );

                Route::group(
                    [
                        'namespace' => 'CelestialObject',
                        'prefix' => 'celestial-objects',
                    ],
                    static function () {
                        Route::get(
                            '/',
                            [
                                'as' => 'api.v1.starmap.celestial-objects.index',
                                'uses' => 'CelestialObjectController@index',
                            ]
                        );
                        Route::get(
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

        Route::group(
            [
                'namespace' => 'Galactapedia',
                'prefix' => 'galactapedia',
            ],
            static function () {
                Route::get(
                    '/',
                    ['as' => 'api.v1.starcitizen.galactapedia.all', 'uses' => 'GalactapediaController@index']
                );
                Route::get(
                    '{article}',
                    ['as' => 'api.v1.starcitizen.galactapedia.show', 'uses' => 'GalactapediaController@show']
                );
                Route::post(
                    '/search',
                    ['as' => 'api.v1.starcitizen.galactapedia.search', 'uses' => 'GalactapediaController@search']
                );
            }
        );
    }
);

Route::group(
    [
        'namespace' => 'Rsi',
    ],
    static function () {
        Route::group(
            [
                'namespace' => 'CommLink',
                'prefix' => 'comm-links',
            ],
            static function () {
                /**
                 * Categories
                 */
                Route::group(
                    [
                        'namespace' => 'Category',
                        'prefix' => 'categories',
                    ],
                    static function () {
                        Route::get(
                            '/',
                            [
                                'as' => 'api.v1.rsi.comm-links.categories.index',
                                'uses' => 'CategoryController@index',
                            ]
                        );
                        Route::get(
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
                Route::group(
                    [
                        'namespace' => 'Channel',
                        'prefix' => 'channels',
                    ],
                    static function () {
                        Route::get(
                            '/',
                            [
                                'as' => 'api.v1.rsi.comm-links.channels.index',
                                'uses' => 'ChannelController@index',
                            ]
                        );
                        Route::get(
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
                Route::group(
                    [
                        'namespace' => 'Series',
                        'prefix' => 'series',
                    ],
                    static function () {
                        Route::get(
                            '/',
                            [
                                'as' => 'api.v1.rsi.comm-links.series.index',
                                'uses' => 'SeriesController@index',
                            ]
                        );
                        Route::get(
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
                Route::post(
                    '/reverse-image-link-search',
                    [
                        'as' => 'api.v1.rsi.comm-links.reverse-image-link-search',
                        'uses' => 'CommLinkSearchController@reverseImageLinkSearch',
                    ]
                );
                Route::post(
                    '/reverse-image-search',
                    [
                        'as' => 'api.v1.rsi.comm-links.reverse-image-search',
                        'uses' => 'CommLinkSearchController@reverseImageSearch',
                    ]
                );
                Route::post(
                    '/search',
                    [
                        'as' => 'api.v1.rsi.comm-links.search',
                        'uses' => 'CommLinkSearchController@searchByTitle',
                    ]
                );
                Route::get(
                    '/',
                    [
                        'as' => 'api.v1.rsi.comm-links.index',
                        'uses' => 'CommLinkController@index',
                    ]
                );
                Route::get(
                    '{comm_link}',
                    [
                        'as' => 'api.v1.rsi.comm-links.show',
                        'uses' => 'CommLinkController@show',
                    ]
                );
            }
        );

        Route::group(
            [
                'namespace' => 'Transcript',
                'prefix' => 'transcripts',
            ],
            static function () {
                Route::get(
                    '/',
                    [
                        'as' => 'api.v1.rsi.transcripts.index',
                        'uses' => 'TranscriptController@index',
                    ]
                );
                Route::get(
                    '{transcript}',
                    [
                        'as' => 'api.v1.rsi.transcripts.show',
                        'uses' => 'TranscriptController@show',
                    ]
                );
            }
        );
    }
);

Route::group(
    [
        'namespace' => 'StarCitizenUnpacked',
    ],
    static function () {
        Route::group(
            [
                'namespace' => 'WeaponPersonal',
                'prefix' => 'weapons',
            ],
            static function () {

                /**
                 * Index
                 */
                Route::get(
                    '/personal',
                    [
                        'as' => 'api.v1.scunpacked.weapons.personal.index',
                        'uses' => 'WeaponPersonalController@index',
                    ]
                );
                Route::get(
                    '/personal/{weapon}',
                    ['as' => 'api.v1.scunpacked.weapons.personal.show', 'uses' => 'WeaponPersonalController@show']
                )->where('weapon', '(.*)');

                /**
                 * Attachments Index
                 */
                Route::get(
                    '/attachments',
                    [
                        'as' => 'api.v1.scunpacked.weapons.attachments.index',
                        'uses' => 'AttachmentController@index',
                    ]
                );
                Route::get(
                    '/attachments/{attachment}',
                    ['as' => 'api.v1.scunpacked.weapons.attachments.show', 'uses' => 'AttachmentController@show']
                )->where('attachment', '(.*)');
            }
        );

        Route::group(
            [
                'prefix' => 'char',
            ],
            static function () {
                Route::group(
                    [
                        'namespace' => 'CharArmor',
                    ],
                    static function () {
                        /**
                         * Index
                         */
                        Route::get(
                            '/armor',
                            [
                                'as' => 'api.v1.scunpacked.char.armor.index',
                                'uses' => 'CharArmorController@index',
                            ]
                        );
                        Route::get(
                            '/armor/{armor}',
                            ['as' => 'api.v1.scunpacked.char.armor.show', 'uses' => 'CharArmorController@show']
                        )->where('armor', '(.*)');
                    }
                );

                Route::group(
                    [],
                    static function () {
                        /**
                         * Index
                         */
                        Route::get(
                            '/clothing',
                            [
                                'as' => 'api.v1.scunpacked.char.clothing.index',
                                'uses' => 'ClothingController@index',
                            ]
                        );

                        Route::get(
                            '/clothing/{clothing}',
                            ['as' => 'api.v1.scunpacked.char.clothing.show', 'uses' => 'ClothingController@show']
                        )->where('clothing', '(.*)');
                    }
                );
            }
        );

        Route::group(
            [
                'namespace' => 'Shop',
                'prefix' => 'shops',
            ],
            static function () {
                /**
                 * Index
                 */
                Route::get(
                    '/',
                    [
                        'as' => 'api.v1.scunpacked.shops.index',
                        'uses' => 'ShopController@index',
                    ]
                );

                Route::get(
                    '/position/{position}',
                    [
                        'as' => 'api.v1.scunpacked.shops.position.show',
                        'uses' => 'ShopController@showPosition',
                    ]
                );

                Route::get(
                    '/name/{name}',
                    [
                        'as' => 'api.v1.scunpacked.shops.name.show',
                        'uses' => 'ShopController@showName',
                    ]
                );

                Route::get(
                    '/{position}/{name}',
                    [
                        'as' => 'api.v1.scunpacked.shops.position.name.show',
                        'uses' => 'ShopController@showShopAtPosition',
                    ]
                );

                Route::get(
                    '/{shop}',
                    ['as' => 'api.v1.scunpacked.shops.show', 'uses' => 'ShopController@show']
                );
            }
        );

        Route::group(
            [
                'namespace' => 'Item',
                'prefix' => 'items',
            ],
            static function () {
                /**
                 * Index
                 */
                Route::get(
                    '/',
                    [
                        'as' => 'api.v1.scunpacked.items.index',
                        'uses' => 'ItemController@index',
                    ]
                );

                Route::get(
                    '/tradeables',
                    [
                        'as' => 'api.v1.scunpacked.items.tradeables.index',
                        'uses' => 'ItemController@indexTradeables',
                    ]
                );

                Route::get(
                    '/{item}',
                    ['as' => 'api.v1.scunpacked.items.show', 'uses' => 'ItemController@show']
                )->where('item', '(.*)');

                Route::post(
                    '/search',
                    ['as' => 'api.v1.scunpacked.items.search', 'uses' => 'ItemController@search']
                );
            }
        );

        Route::group(
            [
                'prefix' => 'food',
            ],
            static function () {
                /**
                 * Index
                 */
                Route::get(
                    '/',
                    [
                        'as' => 'api.v1.scunpacked.food.index',
                        'uses' => 'FoodController@index',
                    ]
                );

                Route::get(
                    '/{food}',
                    ['as' => 'api.v1.scunpacked.food.show', 'uses' => 'FoodController@show']
                )->where('item', '(.*)');

                Route::post(
                    '/search',
                    ['as' => 'api.v1.scunpacked.food.search', 'uses' => 'FoodController@search']
                );
            }
        );

        Route::group(
            [
                'namespace' => 'Ship',
                'prefix' => 'ship-items',
            ],
            static function () {
                /**
                 * Cooler
                 */
                Route::group(
                    [
                        'prefix' => 'coolers',
                    ],
                    static function () {
                        /**
                         * Index
                         */
                        Route::get(
                            '/',
                            [
                                'as' => 'api.v1.scunpacked.ship-items.coolers.index',
                                'uses' => 'CoolerController@index',
                            ]
                        );

                        Route::get(
                            '/{item}',
                            ['as' => 'api.v1.scunpacked.ship-items.coolers.show', 'uses' => 'CoolerController@show']
                        )->where('item', '(.*)');
                    }
                );

                /**
                 * Power Plants
                 */
                Route::group(
                    [
                        'prefix' => 'power-plants',
                    ],
                    static function () {
                        /**
                         * Index
                         */
                        Route::get(
                            '/',
                            [
                                'as' => 'api.v1.scunpacked.ship-items.power-plants.index',
                                'uses' => 'PowerPlantController@index',
                            ]
                        );

                        Route::get(
                            '/{item}',
                            [
                                'as' => 'api.v1.scunpacked.ship-items.power-plants.show',
                                'uses' => 'PowerPlantController@show'
                            ]
                        )->where('item', '(.*)');
                    }
                );

                /**
                 * Quantum Drives
                 */
                Route::group(
                    [
                        'prefix' => 'quantum-drives',
                    ],
                    static function () {
                        /**
                         * Index
                         */
                        Route::get(
                            '/',
                            [
                                'as' => 'api.v1.scunpacked.ship-items.quantum-drives.index',
                                'uses' => 'QuantumDriveController@index',
                            ]
                        );

                        Route::get(
                            '/{item}',
                            [
                                'as' => 'api.v1.scunpacked.ship-items.quantum-drives.show',
                                'uses' => 'QuantumDriveController@show'
                            ]
                        )->where('item', '(.*)');
                    }
                );

                /**
                 * Shields
                 */
                Route::group(
                    [
                        'prefix' => 'shields',
                    ],
                    static function () {
                        /**
                         * Index
                         */
                        Route::get(
                            '/',
                            [
                                'as' => 'api.v1.scunpacked.ship-items.shields.index',
                                'uses' => 'ShieldController@index',
                            ]
                        );

                        Route::get(
                            '/{item}',
                            [
                                'as' => 'api.v1.scunpacked.ship-items.shields.show',
                                'uses' => 'ShieldController@show'
                            ]
                        )->where('item', '(.*)');
                    }
                );

                /**
                 * Weapons
                 */
                Route::group(
                    [
                        'prefix' => 'weapons',
                    ],
                    static function () {
                        /**
                         * Index
                         */
                        Route::get(
                            '/',
                            [
                                'as' => 'api.v1.scunpacked.ship-items.weapons.index',
                                'uses' => 'WeaponController@index',
                            ]
                        );

                        Route::get(
                            '/{item}',
                            [
                                'as' => 'api.v1.scunpacked.ship-items.weapons.show',
                                'uses' => 'WeaponController@show'
                            ]
                        )->where('item', '(.*)');
                    }
                );

                Route::get(
                    '/{item}',
                    ['as' => 'api.v1.scunpacked.ship-items.show', 'uses' => 'ItemController@show']
                )->where('item', '(.*)');

                Route::post(
                    '/search',
                    ['as' => 'api.v1.scunpacked.ship-items.search', 'uses' => 'ItemController@search']
                );
            }
        );
    }
);
