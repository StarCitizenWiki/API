<?php declare(strict_types = 1);

Route::group(
    [],
    function () {
        Route::namespace('Auth')
            ->name('auth.')
            ->group(
                function () {
                    Route::group(
                        [],
                        function () {
                            Route::get('/login', 'LoginController@showLoginForm')->name('login');
                            Route::get('/login/start', 'LoginController@redirectToProvider')->name('login.start');
                            Route::get('/login/callback', 'LoginController@handleProviderCallback')->name(
                                'login.callback'
                            );
                            Route::post('/logout', 'LoginController@logout')->name('logout');
                        }
                    );
                }
            );

        Route::group(
            [],
            function () {
                Route::get('dashboard', 'DashboardController@show')->name('dashboard');


                Route::namespace('Account')
                    ->name('account.')
                    ->group(
                        function () {
                            Route::get('account', 'AccountController@show')->name('show');
                            Route::patch('account', 'AccountController@update')->name('update');
                        }
                    );
                Route::namespace('License')
                    ->name('license.')
                    ->group(
                        function () {
                            Route::get('editor-license', 'LicenseController@show')->name('show');
                            Route::post('editor-license', 'LicenseController@accept')->name('accept');
                        }
                    );

                Route::resources(
                    [
                        'notifications' => 'Notification\NotificationController',
                        'users' => 'User\UserController',
                    ]
                );

                Route::namespace('StarCitizen')
                    ->name('starcitizen.')
                    ->prefix('starcitizen')
                    ->group(
                        function () {
                            Route::resources(
                                [
                                    'manufacturers' => 'Manufacturer\ManufacturerController',
                                    'production-statuses' => 'ProductionStatus\ProductionStatusController',
                                    'production-notes' => 'ProductionNote\ProductionNoteController',
                                ]
                            );

                            Route::prefix('starmap')
                                ->name('starmap.')
                                ->namespace('Starmap')
                                ->group(
                                    function () {
                                        Route::resources(
                                            [
                                                'system' => 'Starsystem\StarsystemController',
                                                'celestial-object' => 'CelestialObject\CelestialObjectController',
                                                'jumppoint' => 'Jumppoint\JumppointController',
                                            ]
                                        );
                                    }
                                );

                            Route::prefix('vehicles')
                                ->name('vehicles.')
                                ->namespace('Vehicle')
                                ->group(
                                    function () {
                                        Route::resources(
                                            [
                                                'ships' => 'Ship\ShipController',
                                                'ground-vehicles' => 'GroundVehicle\GroundVehicleController',
                                                'sizes' => 'Size\SizeController',
                                                'foci' => 'Focus\FocusController',
                                                'types' => 'Type\TypeController',
                                            ]
                                        );
                                    }
                                );
                        }
                    );

                Route::namespace('Rsi')
                    ->name('rsi.')
                    ->prefix('rsi')
                    ->group(
                        function () {
                            Route::namespace('CommLink')
                                ->name('comm-links.')
                                ->prefix('comm-links')
                                ->group(
                                    function () {
                                        Route::get('categories', 'Category\CategoryController@index')->name(
                                            'categories.index'
                                        );
                                        Route::get('categories/{category}', 'Category\CategoryController@show')->name(
                                            'categories.show'
                                        );

                                        Route::get('channels', 'Channel\ChannelController@index')->name(
                                            'channels.index'
                                        );
                                        Route::get('channels/{channel}', 'Channel\ChannelController@show')->name(
                                            'channels.show'
                                        );

                                        Route::get('series', 'Series\SeriesController@index')->name('series.index');
                                        Route::get('series/{series}', 'Series\SeriesController@show')->name(
                                            'series.show'
                                        );
                                    }
                                );

                            Route::resources(
                                [
                                    'comm-links' => 'CommLink\CommLinkController',
                                ]
                            );
                            Route::get(
                                'comm-links/{comm_link}/{version}/preview',
                                'CommLink\CommLinkController@preview'
                            )->name('comm-links.preview');
                        }
                    );
            }
        );
    }
);
