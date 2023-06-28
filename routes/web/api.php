<?php

declare(strict_types=1);

Route::group(
    [],
    static function () {
        Route::get('/', 'PageController@index')->name('index');
        Route::get('/faq', 'PageController@showFaqView')->name('faq');

        Route::get('/comm-links/{commLink}', 'CommLinkController@show')->name('comm-links.show');

        Route::get('/items/{uuid}', 'ItemController@show')->name('items.show');
    }
);
