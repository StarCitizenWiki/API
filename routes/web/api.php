<?php declare(strict_types = 1);

Route::group(
    ['namespace' => 'Api'],
    function () {
        Route::get('/', ['uses' => 'PageController@showApiView'])->name('api_index');
        Route::get('/faq', ['uses' => 'PageController@showFaqView'])->name('api_faq');
        Route::get('/status', ['uses' => 'PageController@showStatusView'])->name('api_status');
    }
);


Route::group(
    ['namespace' => 'User'],
    function () {
        Route::group(
            ['namespace' => 'Auth'],
            function () {
                // Authentication Routes...
                Route::get('login', ['uses' => 'LoginController@showLoginForm'])->name('auth_login_form');
                Route::post('login', ['uses' => 'LoginController@login'])->name('auth_login');
                Route::post('logout', ['uses' => 'LoginController@logout'])->name('auth_logout');

                // Registration Routes...
                Route::get('register', ['uses' => 'RegisterController@showRegistrationForm'])->name('auth_register_form');
                Route::post('register', ['uses' => 'RegisterController@register'])->name('auth_register');

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
                Route::delete('/', ['uses' => 'AccountController@delete'])->name('account_delete');
                Route::patch('/', ['uses' => 'AccountController@updateAccount'])->name('account_update');
                Route::get('edit', ['uses' => 'AccountController@showEditAccountView'])->name('account_edit_form');
                Route::get('delete', ['uses' => 'AccountController@showDeleteAccountView'])->name('account_delete_form');

                Route::group(
                    ['prefix' => 'urls'],
                    function () {
                        Route::get('/', ['uses' => 'ShortUrlController@showUrlsListView'])->name('account_urls_list');
                        Route::post('/', ['uses' => 'ShortUrlController@addUrl'])->name('account_urls_add');
                        Route::delete('/', ['uses' => 'ShortUrlController@deleteUrl'])->name('account_urls_delete');
                        Route::patch('/', ['uses' => 'ShortUrlController@updateUrl'])->name('account_urls_update');
                        Route::get('add', ['uses' => 'ShortUrlController@showAddUrlView'])->name('account_urls_add_form');
                        Route::get('{id}', ['uses' => 'ShortUrlController@showEditUrlView'])->name('account_urls_edit_form');
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
                Route::get('/login', 'LoginController@showLoginForm')->name('admin_login_form');
                Route::post('/login', 'LoginController@login')->name('admin_login');
                Route::post('/logout', 'LoginController@logout')->name('admin_logout');
            }
        );
        Route::group(
            [
                'middleware' => ['admin', 'auth:admin'],
            ],
            function () {
                Route::get('dashboard', ['uses' => 'AdminController@showDashboardView'])->name('admin_dashboard');
                Route::get('logs', ['uses' => 'AdminController@showLogsView'])->name('admin_logs');

                Route::group(
                    ['prefix' => 'notifications'],
                    function () {
                        Route::get('/', ['uses' => 'NotificationController@showNotificationsListView'])->name('admin_notifications_list');
                        Route::post('/', ['uses' => 'NotificationController@addNotification'])->name('admin_notification_add');
                        Route::delete('{id}', ['uses' => 'NotificationController@deleteNotification'])->name('admin_notifications_delete');
                        Route::patch('{id}', ['uses' => 'NotificationController@updateNotification'])->name('admin_notifications_update');
                        Route::post('{id}/restore', ['uses' => 'NotificationController@restoreNotification'])->name('admin_notifications_restore');
                        Route::get('{id}', ['uses' => 'NotificationController@showEditNotificationView'])->name('admin_notifications_edit_form');
                    }
                );

                Route::group(
                    ['prefix' => 'user'],
                    function () {
                        Route::get('/', ['uses' => 'UserController@showUserListView'])->name('admin_user_list');
                        Route::delete('{id}', ['uses' => 'UserController@deleteUser'])->name('admin_user_delete');
                        Route::patch('{id}', ['uses' => 'UserController@updateUser'])->name('admin_user_update');
                        Route::post('{id}/restore', ['uses' => 'UserController@restoreUser'])->name('admin_user_restore');
                        Route::get('{id}', ['uses' => 'UserController@showEditUserView'])->name('admin_user_edit_form');
                        Route::get('{id}/urls', ['uses' => 'ShortUrlController@showUrlsListForUserView'])->name('admin_user_urls_list');
                        Route::get('{id}/requests', ['uses' => 'UserController@showRequestsView'])->name('admin_user_requests_list');
                    }
                );

                Route::get('routes', ['uses' => 'AdminController@showRoutesView'])->name('admin_routes_list');

                Route::group(
                    ['prefix' => 'urls', 'namespace' => 'ShortUrl'],
                    function () {
                        Route::group(
                            ['prefix' => 'whitelist'],
                            function () {
                                Route::get('/', ['uses' => 'ShortUrlWhitelistController@showUrlWhitelistView'])->name('admin_urls_whitelist_list');
                                Route::delete('{id}', ['uses' => 'ShortUrlWhitelistController@deleteWhitelistUrl'])->name('admin_urls_whitelist_delete');
                                Route::post('/', ['uses' => 'ShortUrlWhitelistController@addWhitelistUrl'])->name('admin_urls_whitelist_add');
                                Route::get('add', ['uses' => 'ShortUrlWhitelistController@showAddUrlWhitelistView'])->name('admin_urls_whitelist_add_form');
                            }
                        );
                        Route::get('/', ['uses' => 'ShortUrlController@showUrlsListView'])->name('admin_urls_list');
                        Route::get('{id}', ['uses' => 'ShortUrlController@showEditUrlView'])->name('admin_urls_edit_form');
                        Route::delete('{id}', ['uses' => 'ShortUrlController@deleteUrl'])->name('admin_urls_delete');
                        Route::patch('{id}', ['uses' => 'ShortUrlController@updateUrl'])->name('admin_urls_update');
                        Route::post('{id}', ['uses' => 'ShortUrlController@restoreUrl'])->name('admin_urls_restore');
                    }
                );

                Route::group(
                    ['prefix' => 'starmap'],
                    function () {
                        Route::group(
                            ['prefix' => 'systems'],
                            function () {
                                Route::get('/', ['uses' => 'StarmapController@showStarmapSystemsView'])->name('admin_starmap_systems_list');
                                Route::patch('{id}', ['uses' => 'StarmapController@updateStarmapSystem'])->name('admin_starmap_systems_update');
                                Route::post('/', ['uses' => 'StarmapController@addStarmapSystem'])->name('admin_starmap_systems_add');
                                Route::post('/download', ['uses' => 'StarmapController@downloadStarmap'])->name('admin_starmap_systems_download');
                            }
                        );
                        Route::group(
                            ['prefix' => 'celestialobject'],
                            function () {
                                Route::get('/', ['uses' => 'StarmapController@showStarmapCelestialObjectView'])->name('admin_starmap_celestialobject_list');
                                Route::patch('{id}', ['uses' => 'StarmapController@updateStarmapCelestialobject'])->name('admin_starmap_celestialobject_update');
                                Route::delete('/', ['uses' => 'StarmapController@deleteStarmapCelestialobject'])->name('admin_starmap_celestialobject_delete');
                                Route::post('/', ['uses' => 'StarmapController@addStarmapCelestialobject'])->name('admin_starmap_celestialobject_add');
                            }
                        );
                    }
                );

                Route::group(
                    ['prefix' => 'ships'],
                    function () {
                        Route::get('/', ['uses' => 'ShipsController@showShipsView'])->name('admin_ships_list');
                        Route::post('/download', ['uses' => 'ShipsController@downloadShips'])->name('admin_ships_download');
                    }
                );
            }
        );
    }
);
