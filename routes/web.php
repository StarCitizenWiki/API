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
        Route::get('users', ['uses' => 'AdminController@showUsersListView'])->name('admin_users_list');
        Route::delete('users/{ID}', ['uses' => 'AdminController@deleteUser'])->name('admin_users_delete');
        Route::post('users/{ID}/restore', ['uses' => 'AdminController@restoreUser'])->name('admin_users_restore');
        Route::get('users/{ID}/edit', ['uses' => 'AdminController@showEditUserView'])->name('admin_users_edit_form');
        Route::patch('users/{ID}', ['uses' => 'AdminController@updateUser'])->name('admin_users_update');

        Route::get('routes', ['uses' => 'AdminController@showRoutesView'])->name('admin_routes_list');

        Route::get('urls', ['uses' => 'AdminController@showURLsListView'])->name('admin_urls_list');
        Route::get('urls/whitelist', ['uses' => 'AdminController@showURLWhitelistView'])->name('admin_urls_whitelist_list');
        Route::get('urls/whitelist/add', ['uses' => 'AdminController@showAddURLWhitelistView'])->name('admin_urls_whitelist_add_form');
        Route::delete('urls/whitelist/{id}', ['uses' => 'AdminController@deleteWhitelistURL'])->name('admin_urls_whitelist_delete');
        Route::post('urls/whitelist', ['uses' => 'AdminController@addWhitelistURL'])->name('admin_urls_whitelist_add');
        Route::delete('urls/{ID}', ['uses' => 'AdminController@deleteURL'])->name('admin_urls_delete');
        Route::get('urls/{ID}/edit', ['uses' => 'AdminController@showEditURLView'])->name('admin_urls_edit_form');
        Route::patch('urls/{ID}', ['uses' => 'AdminController@updateURL'])->name('admin_urls_update');
    });

    // Authentication Routes...
    Route::get('login', ['uses' => 'Auth\LoginController@showLoginForm', 'name' => 'login'])->name('auth_login_form');
    Route::post('login', ['uses' => 'Auth\LoginController@login'])->name('auth_login');
    Route::post('logout', ['uses' => 'Auth\LoginController@logout'])->name('auth_logout');

    // Registration Routes...
    Route::get('register', ['uses' => 'Auth\RegisterController@showRegistrationForm'])->name('auth_register_form');
    Route::post('register', ['uses' => 'Auth\RegisterController@register'])->name('auth_register');

    Route::group(['middleware' => 'auth'], function () {
        // Account Routes...
        Route::get('account', ['uses' => 'Auth\AccountController@showAccountView'])->name('account');
        Route::delete('account', ['uses' => 'Auth\AccountController@delete'])->name('account_delete');
        Route::get('account/edit', ['uses' => 'Auth\AccountController@showEditAccountView'])->name('account_edit_form');
        Route::patch('account', ['uses' => 'Auth\AccountController@updateAccount'])->name('account_update');

        Route::get('account/urls', ['uses' => 'Auth\AccountController@showURLsView'])->name('account_urls_list');
        Route::post('account/urls', ['uses' => 'Auth\AccountController@addURL'])->name('account_urls_add');
        Route::get('account/urls/add', ['uses' => 'Auth\AccountController@showAddURLView'])->name('account_urls_add_form');
        Route::delete('account/urls/{ID}', ['uses' => 'Auth\AccountController@deleteURL'])->name('account_urls_delete');
        Route::get('account/urls/{ID}/edit', ['uses' => 'Auth\AccountController@showEditURLView'])->name('account_urls_edit_form');
        Route::patch('account/urls/{ID}', ['uses' => 'Auth\AccountController@updateURL'])->name('account_urls_update');
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

Route::group(['domain' => config('app.shorturl_url'), 'namespace' => 'ShortUrl'], function () {

    Route::get('/', ['uses' => 'ShortUrlController@showShortURLView'])->name('short_url_index');
    Route::group(['middleware' => 'throttle'], function () {
        Route::post('shorten', ['uses' => 'ShortUrlController@createAndRedirect'])->name('short_url_create_redirect');
        Route::get('resolve', ['uses' => 'ShortUrlController@showResolveView'])->name('short_url_resolve_form');
        Route::post('resolve', ['uses' => 'ShortUrlController@resolveAndReturn'])->name('short_url_resolve_return');
        Route::get('{hash_name}', ['uses' => 'ShortUrlController@resolveAndRedirect'])->name('short_url_resolve_redirect');
    });

});

