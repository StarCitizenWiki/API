<?php declare(strict_types = 1);
Route::group(
    ['namespace' => 'ShortUrl'],
    function () {
        Route::get('/', ['uses' => 'ShortUrlWebController@showShortUrlView'])->name('shorturl.index');
        Route::post('/', ['uses' => 'ShortUrlWebController@addUrl'])->name('shorturl.web.create');
        Route::get('resolve', ['uses' => 'ShortUrlWebController@showResolveView'])->name('shorturl.web.resolve_form');
        Route::post('resolve', ['uses' => 'ShortUrlWebController@resolveUrl'])->name('shorturl.web.resolve');
        Route::get('{url_hash}', ['uses' => 'ShortUrlWebController@redirectToUrl'])->name('shorturl.web.redirect');
    }
);
