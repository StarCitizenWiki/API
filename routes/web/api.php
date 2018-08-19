<?php declare(strict_types = 1);

Route::group(
    [],
    function () {
        Route::get('/', 'PageController@showApiView')->name('index');
        Route::get('/faq', 'PageController@showFaqView')->name('faq');
        Route::get('/status', 'PageController@showStatusView')->name('status');
    }
);
