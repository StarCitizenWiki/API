<?php
Route::group(['namespace' => 'ShortURL'], function () {
    Route::post('shorten', ['uses' => 'ShortURLController@create'])->name('shorten');
    Route::post('resolve', ['uses' => 'ShortURLController@resolve']);
});
