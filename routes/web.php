<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});
Route::group([
    'prefix' => 'api',
], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('user-profile', 'AuthController@me');
    Route::post('todo/', 'TodoController@create');
    Route::get('todo/', 'TodoController@findAll');
    Route::get('todo/me/', 'TodoController@findMine');
    Route::get('todo/{id}/', 'TodoController@findOne');
    Route::put('todo/{id}/', 'TodoController@update');
    Route::delete('todo/{id}/', 'TodoController@remove');
    Route::put('todo/completed/{id}/', 'TodoController@markAsCompleted');
});
