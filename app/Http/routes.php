<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::auth();

Route::get('/offline', 'Controller@offline');
Route::get('/not-authorized', 'Controller@notAuthorized');

Route::group(['middleware' => ['not_installed', 'frontend']], function () {
    Route::get('/', 'HomeController@index');

    // User avatar
    Route::get('assets_admin/dist/img/user2-160x160.jpg', 'UserController@avatar');
});

Route::group(['namespace' => 'Admin', 'middleware' => ['backend']], function () {
    Route::get('admin', 'HomeController@index');
});