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

// \Auth::loginUsingId(1, true);

Route::get('/', function () {
    return redirect('/web/song');
});

// auth
Route::name('login')->get('login', 'Auth\LoginController@showLoginForm');
Route::name('logout')->get('logout', 'Auth\LoginController@logout');
Route::post('login', 'Auth\LoginController@login');

Route::get('/home', 'HomeController@index')->name('home');

Route::group([
    'middleware' => ['auth'],
    'namespace' => 'Web\TransactionData',
    'prefix' => 'web'
],function () {
    Route::group([
        'prefix' => 'song',
        'as' => 'song.',
    ],function () {
        Route::get('/', 'SongController@index')->name('index');
        Route::get('/data', 'SongController@data')->name('data');
        Route::get('/spotify', 'SongController@spotify')->name('spotify');
        Route::get('/test', 'SongController@test')->name('test');
        Route::get('/{song}', 'SongController@show')->name('show');
        Route::post('/', 'SongController@post')->name('post');
    });

    Route::group([
        'prefix' => 'artist',
        'as' => 'artist.',
    ],function () {
        Route::get('/', 'ArtistController@index')->name('index');
        Route::get('/data', 'ArtistController@data')->name('data');
        Route::get('/spotify', 'ArtistController@spotify')->name('spotify');
        Route::get('/test', 'ArtistController@test')->name('test');
        Route::get('/{artist}', 'ArtistController@show')->name('show');
        Route::post('/', 'ArtistController@post')->name('post');
    });
});

Route::group([
    'middleware' => ['auth'],
    'namespace' => 'Web\Report',
    'prefix' => 'web'
],function () {
    Route::group([
        'prefix' => 'statistic',
        'as' => 'statistic.',
    ],function () {
        Route::get('/', 'StatisticController@index')->name('index');
        Route::get('/data', 'StatisticController@data')->name('data');
        Route::get('/test', 'StatisticController@test')->name('test');
    });
});