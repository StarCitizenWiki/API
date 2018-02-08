<?php declare(strict_types = 1);

Route::group(
    ['namespace' => 'Api'],
    function () {
        Route::get('/', ['uses' => 'PageController@showApiView'])->name('api.index');
        Route::get('/faq', ['uses' => 'PageController@showFaqView'])->name('api.faq');
        Route::get('/status', ['uses' => 'PageController@showStatusView'])->name('api.status');
    }
);

Route::group(
    ['namespace' => 'User'],
    function () {
        Route::group(
            ['namespace' => 'Auth'],
            function () {
                // Authentication Routes...
                Route::get('login', ['uses' => 'LoginController@showLoginForm'])->name('auth.login_form');
                Route::post('login', ['uses' => 'LoginController@login'])->name('auth.login');
                Route::post('logout', ['uses' => 'LoginController@logout'])->name('auth.logout');

                // Registration Routes...
                Route::get('register', ['uses' => 'RegisterController@showRegistrationForm'])->name(
                    'auth.register_form'
                );
                Route::post('register', ['uses' => 'RegisterController@register'])->name('auth.register');

                Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
                Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
                Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
                Route::post('password/reset', 'ResetPasswordController@reset');
            }
        );

        Route::group(
            ['prefix' => 'account'],
            function () {
                // Account Routes...
                Route::get('/', ['uses' => 'AccountController@showAccountView'])->name('account');
                Route::delete('/', ['uses' => 'AccountController@delete'])->name('account.delete');
                Route::patch('/', ['uses' => 'AccountController@updateAccount'])->name('account.update');
                Route::get('edit', ['uses' => 'AccountController@showEditAccountView'])->name('account.edit_form');
                Route::get('delete', ['uses' => 'AccountController@showDeleteAccountView'])->name(
                    'account.delete_form'
                );

                Route::group(
                    ['prefix' => 'urls'],
                    function () {
                        Route::get('add', ['uses' => 'ShortUrlController@showAddUrlView'])->name(
                            'account.url.add_form'
                        );
                        Route::get('/', ['uses' => 'ShortUrlController@showUrlsListView'])->name('account.url.list');
                        Route::post('/', ['uses' => 'ShortUrlController@addUrl'])->name('account.url.add');
                        Route::delete('{url}', ['uses' => 'ShortUrlController@deleteUrl'])->name('account.url.delete');
                        Route::patch('{url}', ['uses' => 'ShortUrlController@updateUrl'])->name('account.url.update');
                        Route::get('{url}', ['uses' => 'ShortUrlController@showEditUrlView'])->name(
                            'account.url.edit_form'
                        );
                    }
                );
            }
        );
    }
);

Route::group(
    ['namespace' => 'Admin', 'prefix' => 'admin'],
    function () {
        Route::group(
            ['namespace' => 'Auth'],
            function () {
                Route::get('/login', 'LoginController@showLoginForm')->name('admin.login_form');
                Route::post('/login', 'LoginController@login')->name('admin.login');
                Route::post('/logout', 'LoginController@logout')->name('admin.logout');
            }
        );
        Route::group(
            [
                'middleware' => ['admin', 'auth:admin'],
            ],
            function () {
                Route::get('dashboard', ['uses' => 'AdminController@showDashboardView'])->name('admin.dashboard');

                Route::group(
                    ['prefix' => 'logs'],
                    function () {
                        Route::get('/', ['uses' => 'LogController@showLogsView'])->name('admin.logs');
                        Route::patch('/', ['uses' => 'LogController@markLogAsRead'])->name('admin.logs.mark_read');
                    }
                );

                Route::group(
                    ['prefix' => 'notifications'],
                    function () {
                        Route::get('/', ['uses' => 'NotificationController@showNotificationListView'])->name(
                            'admin.notification.list'
                        );
                        Route::get('/add', ['uses' => 'NotificationController@showAddNotificationView'])->name(
                            'admin.notification.add_form'
                        );
                        Route::post('/', ['uses' => 'NotificationController@addNotification'])->name(
                            'admin.notification.add'
                        );
                        Route::delete('{notification}', ['uses' => 'NotificationController@deleteNotification'])->name(
                            'admin.notification.delete'
                        );
                        Route::patch('{notification}', ['uses' => 'NotificationController@updateNotification'])->name(
                            'admin.notification.update'
                        );
                        Route::post(
                            '{notification_with_trashed}/restore',
                            ['uses' => 'NotificationController@restoreNotification']
                        )->name('admin.notification.restore');
                        Route::get(
                            '{notification}',
                            ['uses' => 'NotificationController@showEditNotificationView']
                        )->name('admin.notification.edit_form');
                    }
                );

                Route::group(
                    ['prefix' => 'user'],
                    function () {
                        Route::get('/', ['uses' => 'UserController@showUserListView'])->name('admin.user.list');
                        Route::delete('{user}', ['uses' => 'UserController@deleteUser'])->name('admin.user.delete');
                        Route::patch('{user}', ['uses' => 'UserController@updateUser'])->name('admin.user.update');
                        Route::post('{user_with_trashed}/restore', ['uses' => 'UserController@restoreUser'])->name(
                            'admin.user.restore'
                        );
                        Route::get('{user_with_trashed}', ['uses' => 'UserController@showEditUserView'])->name(
                            'admin.user.edit_form'
                        );
                        Route::get('{user}/urls', ['uses' => 'UserController@showUrlListView'])->name(
                            'admin.user.url.list'
                        );
                        Route::get('{user}/requests', ['uses' => 'UserController@showRequestView'])->name(
                            'admin.user.request.list'
                        );
                    }
                );

                Route::get('requests', ['uses' => 'AdminController@showApiRequestListView'])->name(
                    'admin.request.list'
                );

                Route::group(
                    ['prefix' => 'urls', 'namespace' => 'ShortUrl'],
                    function () {
                        Route::group(
                            ['prefix' => 'whitelist'],
                            function () {
                                Route::get('/', ['uses' => 'ShortUrlWhitelistController@showUrlWhitelistView'])->name(
                                    'admin.url.whitelist.list'
                                );
                                Route::delete(
                                    '{whitelist_url}',
                                    ['uses' => 'ShortUrlWhitelistController@deleteWhitelistUrl']
                                )->name('admin.url.whitelist.delete');
                                Route::post('/', ['uses' => 'ShortUrlWhitelistController@addWhitelistUrl'])->name(
                                    'admin.url.whitelist.add'
                                );
                                Route::get(
                                    'add',
                                    ['uses' => 'ShortUrlWhitelistController@showAddUrlWhitelistView']
                                )->name('admin.url.whitelist.add_form');
                            }
                        );
                        Route::get('/', ['uses' => 'ShortUrlController@showUrlListView'])->name('admin.url.list');
                        Route::get('{url}', ['uses' => 'ShortUrlController@showEditUrlView'])->name(
                            'admin.url.edit_form'
                        );
                        Route::delete('{url}', ['uses' => 'ShortUrlController@deleteUrl'])->name('admin.url.delete');
                        Route::patch('{url}', ['uses' => 'ShortUrlController@updateUrl'])->name('admin.url.update');
                        Route::post('{url}', ['uses' => 'ShortUrlController@restoreUrl'])->name('admin.url.restore');
                    }
                );

                Route::group(
                    ['prefix' => 'starmap'],
                    function () {
                        Route::group(
                            ['prefix' => 'systems'],
                            function () {
                                Route::get('/', ['uses' => 'StarmapController@showStarmapSystemsView'])->name('admin.starmap.systems.list');
                                Route::patch('/', ['uses' => 'StarmapController@updateStarmapSystem'])->name('admin.starmap.systems.update');
                                Route::post('/', ['uses' => 'StarmapController@addStarmapSystem'])->name('admin.starmap.systems.add');
                                Route::post('/download', ['uses' => 'StarmapController@downloadStarmap'])->name('admin.starmap.systems.download');
                                Route::post('/downloadjumppointtunnel', ['uses' => 'JumppointTunnelController@downloadJumppointTunnels'])->name('admin.starmap.jumppointtunnel.download');
                            }
                        );
                        Route::group(
                            ['prefix' => 'celestialobject'],
                            function () {
                                Route::get('/', ['uses' => 'StarmapController@showStarmapCelestialObjectView'])->name('admin.starmap.celestialobject.list');
                                Route::patch('/', ['uses' => 'StarmapController@updateStarmapCelestialobject'])->name('admin.starmap.celestialobject.update');
                                Route::delete('/', ['uses' => 'StarmapController@deleteStarmapCelestialobject'])->name('admin.starmap.celestialobject.delete');
                                Route::post('/', ['uses' => 'StarmapController@addStarmapCelestialobject'])->name('admin.starmap.celestialobject.add');
                            }
                        );
                        Route::group(
                            ['prefix' => 'jumppointtunnel'],
                            function () {
                                Route::get('/', ['uses' => 'JumppointTunnelController@showJumppointTunnelView'])->name('admin.starmap.jumppointtunnel.list');
                            }
                        );
                    }
                );

                Route::group(
                    ['prefix' => 'ships'],
                    function () {
                        Route::get('/', ['uses' => 'ShipsController@showShipsView'])->name('admin.ships.list');
                        Route::post('/download', ['uses' => 'ShipsController@downloadShips'])->name('admin.ships.download');
                    }
                );
            }
        );
    }
);
