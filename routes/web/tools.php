<?php
Route::get('/', ['uses' => 'APIPageController@showAPIView'])->name('tools_index');

Route::group(['namespace' => 'Tools'], function () {
    Route::group(['prefix' => 'tools'], function () {
        Route::get('imageresizer', ['uses' => 'ImageResizeController@showImageResizeView'])->name('tools_imageresizer');
    });

    Route::group(['prefix' => 'media'], function () {
        Route::group(['prefix' => 'images'], function () {
            Route::get('funds', ['uses' => 'FundImageController@getImage', 'type' => FUNDIMAGE_FUNDING_ONLY]);
            Route::group(['prefix' => 'funds'], function () {
                Route::get('text', ['uses' => 'FundImageController@getImage', 'type' => FUNDIMAGE_FUNDING_AND_TEXT]);
                Route::get('bar', ['uses' => 'FundImageController@getImage', 'type' => FUNDIMAGE_FUNDING_AND_BARS]);
            });
        });

        Route::group(['prefix' => 'videos'], function () {

        });
    });
});
