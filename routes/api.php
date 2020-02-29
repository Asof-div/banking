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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1'], function(){

    Route::group(['prefix' => 'customers', 'namespace' => 'Api\v1'], function(){

        Route::get('/', 'CustomerController@index');
        
        Route::post('create', 'CustomerController@create');

        Route::get('show/{account_no}', 'CustomerController@show');
        
        Route::get('check-balance/{account_no}', 'CustomerController@checkBalance');
        
        Route::put('update/{account_no}', 'CustomerController@update');

        Route::put('freeze/{account_no}', 'CustomerController@freezeAccount');
        
        Route::put('unfreeze/{account_no}', 'CustomerController@unfreezeAccount');
    
        Route::delete('delete/{account_no}', 'CustomerController@delete');
    
        Route::get('transactions', 'TransactionController@index');

        Route::get('transactions/{account_no}', 'TransactionController@custom');

        Route::get('transactions/{account_no}/{ref}', 'TransactionController@show');

        Route::post('etransfer/{a_account_no}/{b_account_no}', 'TransactionController@etransfer');

        Route::post('credit-account/{account_no}', 'TransactionController@credit');

        Route::post('atm-account/{account_no}', 'TransactionController@atm');
        
        Route::post('pos-account/{account_no}', 'TransactionController@pos');


    });


});

Route::fallback(function(){
    return response()->json([
        'message' => 'Page Not Found. If error persists, contact info@oyara.com'], 404);
});