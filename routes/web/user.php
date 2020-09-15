<?php declare(strict_types = 1);

Route::group(
    [],
    static function () {
        Route::namespace('Auth')
            ->name('auth.')
            ->group(
                static function () {
                    Route::group(
                        [],
                        static function () {
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
            static function () {
                Route::get('dashboard', 'DashboardController@index')->name('dashboard');
                Route::name('dashboard.')
                    ->prefix('dashboard')
                    ->group(
                        static function () {
                            Route::post(
                                'translate-comm-links',
                                'Job\Rsi\CommLink\JobController@startCommLinkTranslationJob'
                            )->name('translate-comm-links');

                            Route::post(
                                'download-comm-link-images',
                                'Job\Rsi\CommLink\JobController@startCommLinkImageDownloadJob'
                            )->name('download-comm-link-images');

                            Route::post(
                                'download-comm-links',
                                'Job\Rsi\CommLink\JobController@startCommLinkDownloadJob'
                            )->name('download-comm-links');


                            Route::post(
                                'create-wiki-pages',
                                'Job\Wiki\CommLink\JobController@startCommLinkWikiPageCreationJob'
                            )->name('create-wiki-pages');

                            Route::post(
                                'update-proofread-status',
                                'Job\Wiki\CommLink\JobController@startCommLinkProofReadStatusUpdateJob'
                            )->name('update-proofread-status');
                        }
                    );

                Route::namespace('Account')
                    ->name('account.')
                    ->group(
                        static function () {
                            Route::get('account', 'AccountController@index')->name('index');
                            Route::patch('account', 'AccountController@update')->name('update');
                        }
                    );

                Route::resources(
                    [
                        'notifications' => 'Notification\NotificationController',
                        'users' => 'User\UserController',
                        'changelogs' => 'Changelog\ChangelogController',
                        'transcripts' => 'Transcript\TranscriptController',
                    ]
                );

                Route::namespace('StarCitizen')
                    ->name('starcitizen.')
                    ->prefix('starcitizen')
                    ->group(
                        static function () {
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
                                    static function () {
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
                                    static function () {
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
                        static function () {
                            Route::namespace('CommLink')
                                ->name('comm-links.')
                                ->prefix('comm-links')
                                ->group(
                                    static function () {
                                        Route::get('categories', 'Category\CategoryController@index')->name('categories.index');
                                        Route::get('categories/{category}', 'Category\CategoryController@show')->name('categories.show');

                                        Route::get('channels', 'Channel\ChannelController@index')->name('channels.index');
                                        Route::get('channels/{channel}', 'Channel\ChannelController@show')->name('channels.show');

                                        Route::get('series', 'Series\SeriesController@index')->name('series.index');
                                        Route::get('series/{series}', 'Series\SeriesController@show')->name('series.show');

                                        Route::get('images', 'Image\ImageController@index')->name('images.index');

                                        Route::get('reverse-image-search', 'CommLinkController@reverseImageSearch')->name('reverse-image-search');
                                        Route::post('reverse-image-link-search', 'CommLinkController@reverseImageLinkSearchPost')->name('reverse-image-link-search.post');
                                        Route::post('reverse-image-search', 'CommLinkController@reverseImageSearchPost')->name('reverse-image-search.post');
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

                            Route::namespace('Stat')
                                ->name('stat.')

                                ->group(
                                    static function() {
                                        Route::get('stats', 'StatController@index')->name('index');
                                    }
                                );
                        }
                    );
            }
        );
    }
);
