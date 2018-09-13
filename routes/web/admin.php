<?php declare(strict_types = 1);

Route::group(
    [],
    function () {
        Route::namespace('Auth')
            ->name('auth.')
            ->group(base_path('routes/web/admin/auth.php'));

        Route::middleware(['admin', 'auth:admin'])->group(
            function () {
                Route::get('dashboard', 'AdminController@showDashboardView')->name('dashboard');

                Route::get('accept_licence', 'AdminController@acceptLicenseView')->name('accept_license_view');
                Route::post('accept_licence', 'AdminController@acceptLicense')->name('accept_license');

                Route::resources(
                    [
                        'notifications' => 'Notification\NotificationController',
                        'users' => 'User\UserController',
                    ]
                );

                Route::namespace('Log')->group(base_path('routes/web/admin/log.php'));

                Route::namespace('StarCitizen')
                    ->name('starcitizen.')
                    ->prefix('starcitizen')
                    ->group(
                        function () {
                            Route::resources(
                                [
                                    'manufacturers' => 'Manufacturer\ManufacturerController',
                                    'production_statuses' => 'ProductionStatus\ProductionStatusController',
                                    'production_notes' => 'ProductionNote\ProductionNoteController',
                                ]
                            );

                            Route::prefix('starmap')
                                ->name('starmap.')
                                ->namespace('Starmap')
                                ->group(
                                    function () {
                                        Route::resources(
                                            [
                                                'systems' => 'SystemController',
                                                'celestialobjects' => 'CelestialObjectController',
                                                'jumppointtunnel' => 'JumppointTunnelController',
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
                                                'ground_vehicles' => 'GroundVehicle\GroundVehicleController',
                                                'sizes' => 'Size\VehicleSizeController',
                                                'foci' => 'Focus\VehicleFocusController',
                                                'types' => 'Type\VehicleTypeController',
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
                                ->name('comm_links.')
                                ->prefix('comm_links')
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
                                    'comm_links' => 'CommLink\CommLinkController',
                                ]
                            );
                            Route::get('comm_links/{comm_link}/{version}/preview', 'CommLink\CommLinkController@preview')->name('comm_links.preview');
                        }
                    );
            }
        );
    }
);
