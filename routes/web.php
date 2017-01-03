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


Route::get('/kopfbild_tool', function () {
    //include public_path().'/scripts/kopfbild_tool.html';
    return File::get(public_path() . '/../scripts/kopfbild_tool.html');
});


Route::get('/kopfbildtool', ['uses' => 'KopfbildToolController@index']);
