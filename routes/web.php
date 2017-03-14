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
if (App::isLocal() || App::runningUnitTests()) {
    $api_domain = env('APP_URL');
    $tools_domain = env('APP_URL');
    $short_url_domain = env('APP_URL');
} else {
    $api_domain = API_DOMAIN;
    $tools_domain = TOOLS_DOMAIN;
    $short_url_domain = SHORT_URL_DOMAIN;
}

Route::group(['domain' => $api_domain], function () {
    //Route::get('/', ['uses' => 'APIPageController@getIndex']);

    Route::group(['prefix' => 'admin', 'middleware' => 'admin', 'namespace' => 'auth'], function () {
        Route::get('users', ['uses' => 'AdminController@users']);
        Route::delete('users/{ID}/delete', ['uses' => 'AdminController@deleteUser']);
        Route::get('users/{ID}/edit', ['uses' => 'AdminController@editUser']);
        Route::patch('users/{ID}', ['uses' => 'AdminController@patchUser']);
        Route::get('routes', ['uses' => 'AdminController@routes']);
    });

    // Authentication Routes...
    Route::get('login', ['uses' => 'Auth\LoginController@showLoginForm', 'name' => 'login'])->name('login');
    Route::post('login', ['uses' => 'Auth\LoginController@login']);
    Route::post('logout', ['uses' => 'Auth\LoginController@logout'])->name('logout');

    // Registration Routes...
    Route::get('register', ['uses' => 'Auth\RegisterController@showRegistrationForm'])->name('register');
    Route::post('register', ['uses' => 'Auth\RegisterController@register']);

    // Account Routes...
    Route::get('account', ['uses' => 'Auth\AccountController@show', 'middleware' => 'auth'])->name('account');
    Route::delete('account/delete', ['uses' => 'Auth\AccountController@delete', 'middleware' => 'auth'])->name('delete_account');
    Route::get('account/edit', ['uses' => 'Auth\AccountController@showEditForm', 'middleware' => 'auth'])->name('edit_account');
    Route::patch('account/edit', ['uses' => 'Auth\AccountController@patchAccount', 'middleware' => 'auth']);
});

Route::group(['domain' => $tools_domain], function () {
    //Route::get('/', ['uses' => 'APIPageController@getIndex']);

    Route::group(['namespace' => 'Tools'], function () {
        Route::group(['prefix' => 'tools'], function () {
            Route::get('imageresizer', ['uses' => 'ImageResizeController@index']);
        });

        Route::group(['prefix' => 'media', 'middleware' => ['api']], function () {
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

Route::group(['domain' => $short_url_domain, 'namespace' => 'ShortURL'], function () {

    Route::get('/', ['uses' => 'ShortUrlController@getIndex'])->name('short_url_index');
    Route::group(['middleware' => 'throttle'], function () {
        Route::post('/shorten', ['uses' => 'ShortUrlController@shortenURL'])->name('shorten');
        Route::get('{name}', ['uses' => 'ShortUrlController@resolveURL']);
    });

});

