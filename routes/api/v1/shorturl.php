<?php declare(strict_types = 1);
Route::group(
    ['namespace' => 'ShortUrl'],
    function () {
        Route::post('shorten', ['uses' => 'ShortUrlController@create'])->name('shorturl.shorten');
        Route::post('resolve', ['uses' => 'ShortUrlController@resolve']);
    }
);
