<?php declare(strict_types = 1);

Route::group(
    [],
    function () {
        Route::get('/login', 'LoginController@showLoginForm')->name('login_form');
        Route::post('/login', 'LoginController@login')->name('login');
        Route::post('/logout', 'LoginController@logout')->name('logout');
    }
);
