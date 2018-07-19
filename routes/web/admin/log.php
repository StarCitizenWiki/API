<?php declare(strict_types = 1);

Route::prefix('logs')
    ->name('logs.')
    ->group(
        function () {
            Route::get('/', 'LogController@showLogsView')->name('index');
            Route::patch('/', 'LogController@markLogAsRead')->name('mark_read');
        }
    );
