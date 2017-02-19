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
Route::get('/', ['uses' => 'APIPageController@getIndex']);

Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
    Route::get('users', ['uses' => 'AdminController@users']);
    Route::get('routes', ['uses' => 'AdminController@routes']);
});

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