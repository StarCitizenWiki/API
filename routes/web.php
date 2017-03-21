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
    Route::get('/', ['uses' => 'APIPageController@showAPIView'])->name('api_index');;

    Route::group(['prefix' => 'admin', 'middleware' => 'admin', 'namespace' => 'auth'], function () {
        Route::get('users', ['uses' => 'AdminController@showUsersListView'])->name('admin_users_list');
        Route::delete('users/{ID}', ['uses' => 'AdminController@deleteUser']);
        Route::get('users/{ID}/edit', ['uses' => 'AdminController@showEditUserView'])->name('admin_users_edit_form');
        Route::patch('users/{ID}', ['uses' => 'AdminController@updateUser']);

        Route::get('routes', ['uses' => 'AdminController@routes'])->name('admin_routes_list');

        Route::get('urls', ['uses' => 'AdminController@showURLsListView'])->name('admin_urls_list');
        Route::delete('urls/{ID}', ['uses' => 'AdminController@deleteURL']);
        Route::get('urls/{ID}/edit', ['uses' => 'AdminController@showEditURLView'])->name('admin_urls_edit_form');
        Route::patch('urls/{ID}', ['uses' => 'AdminController@updateURL']);
    });

    // Authentication Routes...
    Route::get('login', ['uses' => 'Auth\LoginController@showLoginForm', 'name' => 'login'])->name('login');
    Route::post('login', ['uses' => 'Auth\LoginController@login']);
    Route::post('logout', ['uses' => 'Auth\LoginController@logout'])->name('logout');

    // Registration Routes...
    Route::get('register', ['uses' => 'Auth\RegisterController@showRegistrationForm'])->name('register');
    Route::post('register', ['uses' => 'Auth\RegisterController@register']);

    Route::group(['middleware' => 'auth'], function () {
        // Account Routes...
        Route::get('account', ['uses' => 'Auth\AccountController@showAccountView'])->name('account');
        Route::delete('account/delete', ['uses' => 'Auth\AccountController@delete'])->name('delete_account');
        Route::get('account/edit', ['uses' => 'Auth\AccountController@showEditAccountView'])->name('edit_account');
        Route::patch('account/edit', ['uses' => 'Auth\AccountController@updateAccount']);

        Route::get('account/urls', ['uses' => 'Auth\AccountController@showURLsView'])->name('account_urls_list');
        Route::post('account/urls', ['uses' => 'Auth\AccountController@addURL']);
        Route::get('account/urls/add', ['uses' => 'Auth\AccountController@showAddURLView'])->name('account_urls_add_form');
        Route::delete('account/urls/{ID}', ['uses' => 'Auth\AccountController@deleteURL']);
        Route::get('account/urls/{ID}/edit', ['uses' => 'Auth\AccountController@showEditURLView'])->name('account_urls_edit_form');
        Route::patch('account/urls/{ID}', ['uses' => 'Auth\AccountController@updateURL']);
    });
});

Route::group(['domain' => config('app.tools_url')], function () {
    Route::get('/', ['uses' => 'APIPageController@index'])->name('tools_index');

    Route::group(['namespace' => 'Tools'], function () {
        Route::group(['prefix' => 'tools'], function () {
            Route::get('imageresizer', ['uses' => 'ImageResizeController@showImageResizeView'])->name('imageresizer');
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
        Route::get('{hash_name}', ['uses' => 'ShortUrlController@resolveAndRedirect'])->name('short_url_resolve_redirect');
    });

});

