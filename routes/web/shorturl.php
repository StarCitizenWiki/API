<?php declare(strict_types = 1);
Route::group(
    ['namespace' => 'ShortUrl'],
    function () {
        Route::get(
            '/',
            function () {
                return redirect(config('app.api_url'));
            }
        )->name('shorturl.index');
        Route::get('{url_hash}', ['uses' => 'ShortUrlController@redirectToUrl'])->name('shorturl.redirect');
        Route::get('safe/{url_hash}', ['uses' => 'ShortUrlController@redirectToUrlPreview'])->name(
            'shorturl.redirect.preview'
        );
    }
);
