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

Route::middleware('auth:api')->group(function () {

    // User
    Route::get('/user', function (Request $request) { return $request->user(); });

    // Stats
    Route::get('/stats/balance/{account?}/{date?}', 'Api\StatsController@getBalance');
    Route::get('/stats/expense/{year?}/{month?}/{day?}', 'Api\StatsController@getExpense');
    Route::get('/stats/income/{year?}/{month?}/{day?}', 'Api\StatsController@getIncome');

    // Transactions
    Route::get('/transactions', 'Api\TransactionController@fetch');
    Route::get('/transactions/{transaction}', 'Api\TransactionController@show');
    Route::post('/transactions', 'Api\TransactionController@store');
    Route::match(['put', 'patch'], '/transactions/{transaction}', 'Api\TransactionController@patch');
    Route::delete('/transactions/{transaction}', 'Api\TransactionController@remove');

    // Accounts
    Route::get('/accounts', 'Api\AccountController@fetch');
    Route::get('/accounts/{transaction}', 'Api\AccountController@show');
    Route::post('/accounts', 'Api\AccountController@store');
    Route::match(['put', 'patch'], '/accounts/{transaction}', 'Api\AccountController@patch');
    Route::delete('/accounts/{transaction}', 'Api\AccountController@remove');

});
