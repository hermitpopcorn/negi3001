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

Route::post('/login', 'UserController@login');
Route::post('/register', 'UserController@register');

Route::middleware('auth:api')->group(function () {

    // User
    Route::get('/user', function (Request $request) { return $request->user(); });

    // Stats
    Route::get('/stats/balance/{account?}/{date?}', 'StatsController@getBalance');
    Route::get('/stats/expense/{year?}/{month?}/{day?}', 'StatsController@getExpense');
    Route::get('/stats/income/{year?}/{month?}/{day?}', 'StatsController@getIncome');

    // Transactions
    Route::get('/transactions', 'TransactionController@fetch');
    Route::get('/transactions/{transaction}', 'TransactionController@show');
    Route::post('/transactions', 'TransactionController@store');
    Route::match(['put', 'patch'], '/transactions/{transaction}', 'TransactionController@patch');
    Route::delete('/transactions/{transaction}', 'TransactionController@remove');

    // Accounts
    Route::get('/accounts', 'AccountController@fetch');
    Route::get('/accounts/{transaction}', 'AccountController@show');
    Route::post('/accounts', 'AccountController@store');
    Route::match(['put', 'patch'], '/accounts/{transaction}', 'AccountController@patch');
    Route::delete('/accounts/{transaction}', 'AccountController@remove');

});
