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

// See "API resource routes" for more information on this helper:
// https://laravel.com/docs/6.x/controllers#restful-partial-resource-routes
Route::apiResource('contacts', 'ContactsController');
Route::apiResource('solar_projects', 'SolarProjectsController');

// None of the ready-made resource helpers implemented these relationship routes
// the way we want them, so just list them out manually
Route::get('/solar_projects/{solar_project}/contacts', 'SolarProjectContactsController@index');
Route::put('/solar_projects/{solar_project}/contacts', 'SolarProjectContactsController@update');
//routes for bulk operations
//Route::get('/solar_projects/bulk/{method}/{ids}', 'SolarProjectsController@destroyMany'); //initial phase in testing
//Note: ids are now passed through delere body
Route::delete('/solar_projects/bulk/{method}', 'SolarProjectsController@destroyMany');

