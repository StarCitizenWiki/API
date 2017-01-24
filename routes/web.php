<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/kopfbildtool', ['uses' => 'KopfbildToolController@index']);

Route::group(['namespace' => 'StarCitizen', 'prefix' => 'apiv1'], function () {
   Route::get('crowdfunding', ['uses' => 'StatsAPIController@getStatsAsJSON']);
});