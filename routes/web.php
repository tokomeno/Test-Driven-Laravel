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

Route::get('mockups/order', function () {
	return view('orders.show');
});

Route::get('/concerts/{id}', 'ConcertsController@show');
Route::post('/concerts/{id}/orders', 'ConcertOrdersController@store');

Route::get('/orders/{confirmationNumber}', 'OrderController@show');

Auth::routes();


Route::post('/login', 'Auth\LoginController@login');
Route::post('/loginout', 'Auth\LoginController@logout')->name('auth.logout');


Route::group(['middleware' => 'auth'], function () {
	Route::get("/backstage/concerts/new", 'Backstage\ConcertsController@create');
	Route::get("/backstage/concerts", 'Backstage\ConcertsController@index')->name('backstage.concerts.index');
});
