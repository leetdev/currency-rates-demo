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


// Authentication
Route::get('login/{provider?}', 'LoginController@login')->name('login');
Route::get('login/{provider?}/callback', 'LoginController@auth')->name('auth');
Route::get('logout', 'LoginController@logout')->name('logout');

// Calculations CRUD & display
Route::resource('', 'CalculationController', ['parameters' => ['' => 'calculation']]);
Route::get('/{calculation}/favourite', 'CalculationController@favourite')->name('favourite');
