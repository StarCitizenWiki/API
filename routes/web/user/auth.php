<?php declare(strict_types = 1);

Route::group(
    [],
    function () {
        // Authentication Routes...
        Route::get('login', 'LoginController@showLoginForm')->name('login_form');
        Route::post('login', 'LoginController@login')->name('login');
        Route::post('logout', 'LoginController@logout')->name('logout');

        // Registration Routes...
        Route::get('register', 'RegisterController@showRegistrationForm')->name('register_form');
        Route::post('register', 'RegisterController@register')->name('register');

        Route::prefix('password')
            ->name('password.')
            ->group(
                function () {
                    Route::get('reset', 'ForgotPasswordController@showLinkRequestForm')->name('request');
                    Route::post('email', 'ForgotPasswordController@sendResetLinkEmail')->name('email');
                    Route::get('reset/{token}', 'ResetPasswordController@showResetForm')->name('reset');
                    Route::post('reset', 'ResetPasswordController@reset');
                }
            );
    }
);
