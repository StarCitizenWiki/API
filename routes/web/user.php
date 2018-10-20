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
                Route::get('dashboard', 'DashboardController@index')->name('dashboard');
                Route::name('dashboard.')
                    ->prefix('dashboard')
                    ->group(
                        function () {
                            Route::post(
                                'translate-comm-links',
                                'DashboardController@startCommLinkTranslationJob'
                            )->name('translate-comm-links');

                            Route::post(
                                'create-wiki-pages',
                                'DashboardController@startCommLinkWikiPageCreationJob'
                            )->name('create-wiki-pages');

                            Route::post(
                                'download-comm-link-images',
                                'DashboardController@startCommLinkIMageDownloadJob'
                            )->name('download-comm-link-images');

                            Route::post(
                                'download-comm-links',
                                'DashboardController@startCommLinkDownloadJob'
                            )->name('download-comm-links');
                        }
                    );

                Route::namespace('Account')
                    ->name('account.')
                    ->group(
                        function () {
                            Route::get('account', 'AccountController@index')->name('index');
                            Route::patch('account', 'AccountController@update')->name('update');
                        }
                    );

                Route::resources(
                    [
                        'notifications' => 'Notification\NotificationController',
                        'users' => 'User\UserController',
                        'changelogs' => 'Changelog\ChangelogController',
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
                                        Route::get('categories', 'Category\CategoryController@index')->name('categories.index');
                                        Route::get('categories/{category}', 'Category\CategoryController@show')->name('categories.show');

                                        Route::get('channels', 'Channel\ChannelController@index')->name('channels.index');
                                        Route::get('channels/{channel}', 'Channel\ChannelController@show')->name('channels.show');

                                        Route::get('series', 'Series\SeriesController@index')->name('series.index');
                                        Route::get('series/{series}', 'Series\SeriesController@show')->name('series.show');

                                        Route::get('images', 'Image\ImageController@index')->name('images.index');
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
