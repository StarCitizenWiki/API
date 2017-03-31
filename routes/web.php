<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['domain' => config('app.api_url')], function () {
    Route::get('/', ['uses' => 'APIPageController@showAPIView'])->name('api_index');

    Route::group(['prefix' => 'admin', 'middleware' => 'admin', 'namespace' => 'auth'], function () {
        Route::group(['prefix' => 'users'], function () {
            Route::get('/', ['uses' => 'AdminController@showUsersListView'])->name('admin_users_list');
            Route::delete('/', ['uses' => 'AdminController@deleteUser'])->name('admin_users_delete');
            Route::patch('/', ['uses' => 'AdminController@updateUser'])->name('admin_users_update');
            Route::post('restore', ['uses' => 'AdminController@restoreUser'])->name('admin_users_restore');
            Route::get('{ID}', ['uses' => 'AdminController@showEditUserView'])->name('admin_users_edit_form');
        });

        Route::get('routes', ['uses' => 'AdminController@showRoutesView'])->name('admin_routes_list');

        Route::group(['prefix' => 'urls'], function () {
            Route::get('/', ['uses' => 'AdminController@showURLsListView'])->name('admin_urls_list');
            Route::delete('/', ['uses' => 'AdminController@deleteURL'])->name('admin_urls_delete');
            Route::patch('/', ['uses' => 'AdminController@updateURL'])->name('admin_urls_update');
            Route::get('whitelist', ['uses' => 'AdminController@showURLWhitelistView'])->name('admin_urls_whitelist_list');
            Route::get('whitelist/add', ['uses' => 'AdminController@showAddURLWhitelistView'])->name('admin_urls_whitelist_add_form');
            Route::delete('whitelist', ['uses' => 'AdminController@deleteWhitelistURL'])->name('admin_urls_whitelist_delete');
            Route::post('whitelist', ['uses' => 'AdminController@addWhitelistURL'])->name('admin_urls_whitelist_add');
            Route::get('{ID}', ['uses' => 'AdminController@showEditURLView'])->name('admin_urls_edit_form');
        });
    });

    // Authentication Routes...
    Route::get('login', ['uses' => 'Auth\LoginController@showLoginForm', 'name' => 'login'])->name('auth_login_form');
    Route::post('login', ['uses' => 'Auth\LoginController@login'])->name('auth_login');
    Route::post('logout', ['uses' => 'Auth\LoginController@logout'])->name('auth_logout');

    // Registration Routes...
    Route::get('register', ['uses' => 'Auth\RegisterController@showRegistrationForm'])->name('auth_register_form');
    Route::post('register', ['uses' => 'Auth\RegisterController@register'])->name('auth_register');

    Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {
        // Account Routes...
        Route::get('/', ['uses' => 'Auth\AccountController@showAccountView'])->name('account');
        Route::delete('/', ['uses' => 'Auth\AccountController@delete'])->name('account_delete');
        Route::patch('/', ['uses' => 'Auth\AccountController@updateAccount'])->name('account_update');
        Route::get('edit', ['uses' => 'Auth\AccountController@showEditAccountView'])->name('account_edit_form');

        Route::group(['prefix' => 'urls'], function () {
            Route::get('/', ['uses' => 'Auth\AccountController@showURLsView'])->name('account_urls_list');
            Route::post('/', ['uses' => 'Auth\AccountController@addURL'])->name('account_urls_add');
            Route::delete('/', ['uses' => 'Auth\AccountController@deleteURL'])->name('account_urls_delete');
            Route::patch('/', ['uses' => 'Auth\AccountController@updateURL'])->name('account_urls_update');
            Route::get('add', ['uses' => 'Auth\AccountController@showAddURLView'])->name('account_urls_add_form');
            Route::get('{ID}', ['uses' => 'Auth\AccountController@showEditURLView'])->name('account_urls_edit_form');
        });
    });
});

Route::group(['domain' => config('app.tools_url')], function () {
    Route::get('/', ['uses' => 'APIPageController@index'])->name('tools_index');

    Route::group(['namespace' => 'Tools'], function () {
        Route::group(['prefix' => 'tools'], function () {
            Route::get('imageresizer', ['uses' => 'ImageResizeController@showImageResizeView'])->name('tools_imageresizer');
        });

        Route::group(['prefix' => 'media', 'middleware' => ['throttle','token_usage']], function () {
            Route::group(['prefix' => 'images'], function () {
                Route::get('funds', ['uses' => 'FundImageController@getImage', 'type' => FUNDIMAGE_FUNDING_ONLY]);
                Route::group(['prefix' => 'funds'], function () {
                    Route::get('text', ['uses' => 'FundImageController@getImage', 'type' => FUNDIMAGE_FUNDING_AND_TEXT]);
                    Route::get('bar', ['uses' => 'FundImageController@getImage', 'type' => FUNDIMAGE_FUNDING_AND_BARS]);
                });
            });

            Route::group(['prefix' => 'videos'], function () {

            });
        });
    });
});

Route::group(['domain' => config('app.shorturl_url'), 'namespace' => 'ShortURL'], function () {

    Route::get('/', ['uses' => 'ShortURLController@showShortURLView'])->name('short_url_index');
    Route::group(['middleware' => 'throttle'], function () {
        Route::post('shorten', ['uses' => 'ShortURLController@createAndRedirect'])->name('short_url_create_redirect');
        Route::get('resolve', ['uses' => 'ShortURLController@showResolveView'])->name('short_url_resolve_form');
        Route::post('resolve', ['uses' => 'ShortURLController@resolveAndDisplay'])->name('short_url_resolve_display');
        Route::get('{hash_name}', ['uses' => 'ShortURLController@resolveAndRedirect'])->name('short_url_resolve_redirect');
    });
});
