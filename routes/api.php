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

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/



//************** SIGNUP
Route::post('/signup',[
    'uses' => 'userController@signup'
]);

Route::get('/test',[
    'uses' => 'userController@test'
]);

Route::post('/signin',[
    'uses' => 'userController@signin'
]);


//*************** LOGGED IN PROFILE REQUIRES TOKEN */
Route::get('/user/profile',[
    'middleware' => 'auth.jwt',
    'uses' => 'userController@getprofile'
]);

Route::post('/user/addskills',[
    'middleware' => 'auth.jwt',
    'uses' => 'userController@addskills',
]);

Route::post('/user/removeskills',[
    'middleware' => 'auth.jwt',
    'uses' => 'userController@removeskills',
]);


Route::post('/user/clearskills',[
    'middleware' => 'auth.jwt',
    'uses' => 'userController@clearskills',
]);

// /user/updatename   /user/updatedescription /user/updateoccupation  /user/updateinstitution
Route::post('/user/update{attribute}',[
    'middleware' => 'auth.jwt',
    'uses' => 'userController@updateattribute',
]);

Route::post('/user/uploadavatar',[
    'middleware' => 'auth.jwt',
    'uses' => 'userController@uploadavatar',
]);

Route::get('/user/history',[
    'middleware' => 'auth.jwt',
    'uses' => 'userController@gethistory',
]);


/***********************************  PUBLIC PROFILES */
Route::get('/user/getavatar',[
    'middleware' => 'auth.jwt',
    'uses' => 'userController@getavatar',
]);