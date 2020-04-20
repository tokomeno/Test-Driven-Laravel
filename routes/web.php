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

Route::get('/concerts/{id}', 'ConcertsController@show')->name('concerts.show');
Route::post('/concerts/{id}/orders', 'ConcertOrdersController@store');

Route::get('/orders/{confirmationNumber}', 'OrderController@show');

Auth::routes();


Route::post('/login', 'Auth\LoginController@login');
Route::post('/loginout', 'Auth\LoginController@logout')->name('auth.logout');


Route::group(['middleware' => 'auth', 'prefix' => 'backstage', 'namespace' => 'Backstage'], function () {
	Route::get("/concerts", 'ConcertsController@index')->name('backstage.concerts.index');
	Route::get("/concerts/new", 'ConcertsController@create');
	Route::post("/concerts", 'ConcertsController@store')->name('backstage.concerts.store');
	Route::get("/concerts/{id}/edit", 'ConcertsController@edit')->name('backstage.concerts.edit');
	Route::patch("/concerts/{id}", 'ConcertsController@update')->name('backstage.concerts.update');
});
