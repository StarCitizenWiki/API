<?php declare(strict_types = 1);
Route::get('/', ['uses' => 'ApiPageController@showApiView'])->name('tools.index');

Route::group(
    ['namespace' => 'Tool'],
    function () {
        Route::group(
            ['prefix' => 'tools'],
            function () {
                Route::get('imageresizer', ['uses' => 'ImageResizeController@showImageResizeView'])->name(
                    'tools.imageresizer'
                );
            }
        );

        Route::group(
            ['prefix' => 'media'],
            function () {
                Route::group(
                    ['prefix' => 'images'],
                    function () {
                        Route::get(
                            'funds',
                            [
                                'uses' => 'FundImageController@getImage',
                                'type' => config('tools.fundimage.type.funding'),
                            ]
                        );
                        Route::group(
                            ['prefix' => 'funds'],
                            function () {
                                Route::get(
                                    'text',
                                    [
                                        'uses' => 'FundImageController@getImage',
                                        'type' => config('tools.fundimage.type.text'),
                                    ]
                                );
                                Route::get(
                                    'bar',
                                    [
                                        'uses' => 'FundImageController@getImage',
                                        'type' => config('tools.fundimage.type.bars'),
                                    ]
                                );
                            }
                        );
                    }
                );

                Route::group(
                    ['prefix' => 'videos'],
                    function () {

                    }
                );
            }
        );
    }
);
