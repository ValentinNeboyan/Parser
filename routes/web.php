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

Route::get('/options', 'ParserController@getOptions')->name('parser.options');
Route::get('/links', 'ParserController@getLinks')->name('parser.links');
Route::get('/pageCount', 'ParserController@pageCount')->name('parser.pageCount');
Route::get('/getOffers', 'ParserController@getOffers')->name('parser.offers');
Route::get('/start', 'ParserController@start')->name('parser.start');


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
