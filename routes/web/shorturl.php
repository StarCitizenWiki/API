<?php declare(strict_types = 1);
Route::group(
    ['namespace' => 'ShortUrl'],
    function () {
        Route::get('/', ['uses' => 'ShortUrlController@showShortUrlView'])->name('short_url_index');
        Route::post('shorten', ['uses' => 'ShortUrlController@createAndRedirect'])->name('short_url_create_redirect');
        Route::get('resolve', ['uses' => 'ShortUrlController@showResolveView'])->name('short_url_resolve_form');
        Route::post('resolve', ['uses' => 'ShortUrlController@resolveAndDisplay'])->name('short_url_resolve_display');
        Route::get('{hash}', ['uses' => 'ShortUrlController@resolveAndRedirect'])->name(
            'short_url_resolve_redirect'
        );
    }
);
