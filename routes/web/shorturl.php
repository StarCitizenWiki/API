<?php declare(strict_types = 1);
Route::group(
    ['namespace' => 'ShortUrl'],
    function () {
        Route::get('/', ['uses' => 'ShortUrlWebController@showShortUrlView'])->name('short_url_index');
        Route::post('/', ['uses' => 'ShortUrlWebController@addUrl'])->name('short_url_web_create');
        Route::get('resolve', ['uses' => 'ShortUrlWebController@showResolveView'])->name('short_url_web_resolve_form');
        Route::post('resolve', ['uses' => 'ShortUrlWebController@resolveUrl'])->name('short_url_web_resolve');
        Route::get('{url_hash}', ['uses' => 'ShortUrlWebController@redirectToUrl'])->name('short_url_web_redirect');
    }
);
