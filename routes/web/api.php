<?php
Route::get('/', ['uses' => 'APIPageController@showAPIView'])->name('api_index');

Route::group(['prefix' => 'admin', 'middleware' => ['admin'], 'namespace' => 'Auth\Admin'], function () {
    Route::get('/', ['uses' => 'AdminController@showDashboardView'])->name('admin_dashboard');
    Route::get('logs', ['uses' => 'AdminController@showLogsView'])->name('admin_logs');

    Route::group(['prefix' => 'users'], function () {
        Route::get('/', ['uses' => 'UserController@showUsersListView'])->name('admin_users_list');
        Route::delete('/', ['uses' => 'UserController@deleteUser'])->name('admin_users_delete');
        Route::patch('/', ['uses' => 'UserController@updateUser'])->name('admin_users_update');
        Route::post('restore', ['uses' => 'UserController@restoreUser'])->name('admin_users_restore');
        Route::get('{ID}', ['uses' => 'UserController@showEditUserView'])->name('admin_users_edit_form');
        Route::get('{ID}/urls', ['uses' => 'ShortURLController@showURLsListForUserView'])->name('admin_users_urls_list');
        Route::get('{ID}/requests', ['uses' => 'UserController@showRequestsView'])->name('admin_users_requests_list');
    });

    Route::get('routes', ['uses' => 'AdminController@showRoutesView'])->name('admin_routes_list');

    Route::group(['prefix' => 'urls'], function () {
        Route::get('/', ['uses' => 'ShortURLController@showURLsListView'])->name('admin_urls_list');
        Route::delete('/', ['uses' => 'ShortURLController@deleteURL'])->name('admin_urls_delete');
        Route::patch('/', ['uses' => 'ShortURLController@updateURL'])->name('admin_urls_update');
        Route::get('whitelist', ['uses' => 'ShortURLController@showURLWhitelistView'])->name('admin_urls_whitelist_list');
        Route::get('whitelist/add', ['uses' => 'ShortURLController@showAddURLWhitelistView'])->name('admin_urls_whitelist_add_form');
        Route::delete('whitelist', ['uses' => 'ShortURLController@deleteWhitelistURL'])->name('admin_urls_whitelist_delete');
        Route::post('whitelist', ['uses' => 'ShortURLController@addWhitelistURL'])->name('admin_urls_whitelist_add');
        Route::get('{ID}', ['uses' => 'ShortURLController@showEditURLView'])->name('admin_urls_edit_form');
    });

    Route::group(['prefix' => 'starmap'], function () {
        Route::group(['prefix' => 'systems'], function () {
            Route::get('/', ['uses' => 'StarmapController@showStarmapSystemsView'])->name('admin_starmap_systems_list');
            Route::patch('/', ['uses' => 'StarmapController@updateStarmapSystem'])->name('admin_starmap_systems_update');
            Route::delete('/', ['uses' => 'StarmapController@deleteStarmapSystem'])->name('admin_starmap_systems_delete');
            Route::post('/', ['uses' => 'StarmapController@addStarmapSystem'])->name('admin_starmap_systems_add');
            Route::get('add', ['uses' => 'StarmapController@showAddStarmapSystemsView'])->name('admin_starmap_systems_add_form');
            Route::get('{code}', ['uses' => 'StarmapController@showEditStarmapSystemsView'])->name('admin_starmap_systems_edit_form');
            Route::post('/download', ['uses' => 'StarmapController@downloadStarmap'])->name('admin_starmap_systems_download');
        });
    });

    Route::group(['prefix' => 'ships'], function () {
        Route::get('/', ['uses' => 'ShipsController@showShipsView'])->name('admin_ships_list');
        Route::post('/download', ['uses' => 'ShipsController@downloadShips'])->name('admin_ships_download');
    });
});

Route::group(['namespace' => 'Auth'], function () {
    // Authentication Routes...
    Route::get('login', ['uses' => 'LoginController@showLoginForm', 'name' => 'login'])->name('auth_login_form');
    Route::post('login', ['uses' => 'LoginController@login'])->name('auth_login');
    Route::post('logout', ['uses' => 'LoginController@logout'])->name('auth_logout');

    // Registration Routes...
    Route::get('register', ['uses' => 'RegisterController@showRegistrationForm'])->name('auth_register_form');
    Route::post('register', ['uses' => 'RegisterController@register'])->name('auth_register');

    Route::group(['middleware' => ['auth'], 'prefix' => 'account', 'namespace' => 'Account'], function () {
        // Account Routes...
        Route::get('/', ['uses' => 'AccountController@showAccountView'])->name('account');
        Route::delete('/', ['uses' => 'AccountController@delete'])->name('account_delete');
        Route::patch('/', ['uses' => 'AccountController@updateAccount'])->name('account_update');
        Route::get('edit', ['uses' => 'AccountController@showEditAccountView'])->name('account_edit_form');

        Route::group(['prefix' => 'urls'], function () {
            Route::get('/', ['uses' => 'ShortURLController@showURLsListView'])->name('account_urls_list');
            Route::post('/', ['uses' => 'ShortURLController@addURL'])->name('account_urls_add');
            Route::delete('/', ['uses' => 'ShortURLController@deleteURL'])->name('account_urls_delete');
            Route::patch('/', ['uses' => 'ShortURLController@updateURL'])->name('account_urls_update');
            Route::get('add', ['uses' => 'ShortURLController@showAddURLView'])->name('account_urls_add_form');
            Route::get('{ID}', ['uses' => 'ShortURLController@showEditURLView'])->name('account_urls_edit_form');
        });
    });
});
