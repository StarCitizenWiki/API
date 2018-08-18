<?php declare(strict_types = 1);

Route::group(
    [],
    function () {
        Route::get('/login', 'LoginController@showLoginForm')->name('login');
        Route::get('/login/start', 'LoginController@redirectToProvider')->name('login.start');
        Route::get('/login/callback', 'LoginController@handleProviderCallback')->name('login.callback');
        Route::post('/logout', 'LoginController@logout')->name('logout');
    }
);
