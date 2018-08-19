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
            }
        );
    }
);
