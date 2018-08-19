<?php declare(strict_types = 1);

Route::group(
    [],
    function () {
        Route::namespace('Auth')
            ->name('auth.')
            ->group(base_path('routes/web/user/auth.php'));

        // Account Routes...
        Route::prefix('account')
            ->name('account.')
            ->group(
                function () {
                    Route::get('/', 'AccountController@index')->name('index');
                    Route::get('/edit', 'AccountController@edit')->name('edit');
                    Route::patch('/', 'AccountController@update')->name('update');
                    Route::delete('/', 'AccountController@destroy')->name('destroy');
                    Route::get('/delete', 'AccountController@delete')->name('delete');
                }
            );
    }
);
