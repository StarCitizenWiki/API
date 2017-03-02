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
        Route::delete('users/{ID}', ['uses' => 'AdminController@deleteUser']);
        Route::get('users/{ID}/edit', ['uses' => 'AdminController@editUser']);
        Route::patch('users/{ID}', ['uses' => 'AdminController@patchUser']);

        Route::get('routes', ['uses' => 'AdminController@routes']);

        Route::get('urls', ['uses' => 'AdminController@urls']);
        Route::delete('urls/{ID}', ['uses' => 'AdminController@deleteURL']);
        Route::get('urls/{ID}/edit', ['uses' => 'AdminController@editURL']);
        Route::patch('urls/{ID}', ['uses' => 'AdminController@patchURL']);
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
        Route::get('account', ['uses' => 'Auth\AccountController@show'])->name('account');
        Route::delete('account/delete', ['uses' => 'Auth\AccountController@delete'])->name('delete_account');
        Route::get('account/edit', ['uses' => 'Auth\AccountController@editAccount'])->name('edit_account');
        Route::patch('account/edit', ['uses' => 'Auth\AccountController@patchAccount']);

        Route::get('account/urls', ['uses' => 'Auth\AccountController@showURLs']);
        Route::delete('account/urls/{ID}', ['uses' => 'Auth\AccountController@deleteURL']);
        Route::get('account/urls/{ID}/edit', ['uses' => 'Auth\AccountController@editURL']);
        Route::patch('account/urls/{ID}', ['uses' => 'Auth\AccountController@patchURL']);
    });
});

Route::group(['domain' => $tools_domain], function () {
    //Route::get('/', ['uses' => 'APIPageController@getIndex']);

    Route::group(['namespace' => 'Tools'], function () {
        Route::group(['prefix' => 'tools'], function () {
            Route::get('imageresizer', ['uses' => 'ImageResizeController@index']);
        });

        Route::group(['prefix' => 'media', 'middleware' => ['token_usage', 'throttle']], function () {
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

Route::group(['domain' => $short_url_domain, 'namespace' => 'ShortUrl'], function () {

    Route::get('/', ['uses' => 'ShortUrlController@show'])->name('short_url_index');
    Route::group(['middleware' => 'throttle'], function () {
        Route::post('/shorten', ['uses' => 'ShortUrlController@create'])->name('shorten');
        Route::get('{name}', ['uses' => 'ShortUrlController@resolve']);
    });

});
