<?php

use App\Http\Controllers\Api\Game\ItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(
    [],
    static function () {

        //        Route::get('manufacturers', 'ManufacturerController@index')->name('manufacturers.index');
        //        Route::post('manufacturers/search', 'ManufacturerController@search')->name('manufacturers.search');
        //        Route::get('manufacturers/{manufacturer}', 'ManufacturerController@show')->name('manufacturers.show');

        //        Route::post('items/search', 'ItemController@search')->name('items.search');
        //        Route::post('items/{gameVersion}/search', 'ItemController@search')->name('items.search.versioned');
        Route::get('items', [ItemController::class, 'index'])->name('items.index');
        Route::get('items/{item}', [ItemController::class, 'show'])->name('items.show')->where('item', '.*');
    }
);
