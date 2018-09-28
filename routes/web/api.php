<?php declare(strict_types = 1);

Route::group(
    [],
    function () {
        Route::get('/', 'PageController@index')->name('index');
        Route::get('/faq', 'PageController@showFaqView')->name('faq');
        Route::get('/status', 'PageController@showStatusView')->name('status');

        Route::get('/comm-link/{comm_link}', 'CommLinkController@show')->name('comm-link.show');
    }
);
