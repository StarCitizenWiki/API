<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['namespace' => 'StarCitizen', 'prefix' => 'v1', 'middleware' => ['api', 'throttle:60,1']], function () {
    Route::get('crowdfunding', ['uses' => 'StatsAPIController@getStatsAsJSON']);
});
