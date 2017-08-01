<?php
Route::group(['namespace' => 'ShortURL'], function () {
    Route::get('/', ['uses' => 'ShortURLController@showShortURLView'])->name('short_url_index');
    Route::post('shorten', ['uses' => 'ShortURLController@createAndRedirect'])->name('short_url_create_redirect');
    Route::get('resolve', ['uses' => 'ShortURLController@showResolveView'])->name('short_url_resolve_form');
    Route::post('resolve', ['uses' => 'ShortURLController@resolveAndDisplay'])->name('short_url_resolve_display');
    Route::get('{hash_name}', ['uses' => 'ShortURLController@resolveAndRedirect'])->name('short_url_resolve_redirect');
});
