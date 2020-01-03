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



//  takes json with  'name' / 'skills'/ 'description' / 'id' / 'score' greater than / 'hp' greater than ... returns matching profiles
Route::post('/profile/search',[
    'uses' => 'profileController@search',
]);

Route::get('/profile/all',[
    'uses' => 'profileController@allprofiles',
]);

Route::get('/profile{id}',[
    'uses' => 'ProfileController@getprofile',
]);

Route::get('/profile{id}/getavatar',[
    'uses' => 'ProfileController@getavatar',
]);





/***************************** HELP ME FUNCTIONS (CRUD) */

// helpme with status 'open' or 'selective' are public (everyone can see them).
// helpme with status 'pending' succeeded' 'failed' are only seen by request maker and helper.

Route::post('/user/helpme/create',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpmeController@createhelpme',
]);


Route::post('/user/helpme{id}/delete',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpmeController@deletehelpme',
]);

Route::get('/user/helpme/all',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpmeController@getallhelpme',
]);


Route::post('/user/helpme{id}/addskills',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpmeController@addskills',
]);

Route::post('/user/helpme{id}/removeskills',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpmeController@removeskills',
]);


//updates title / short_description / description / status / cost
Route::post('/user/helpme{id}/update',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpmeController@updatehelpme',
]);


Route::get('/user/helpme{id}',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpmeController@getmyhelpme',
]);

// public get
Route::get('/helpme{id}',[
    'uses' => 'HelpmeController@gethelpme',
]);

// helper_id to accept
Route::post('/user/helpme{id}/selecthelper',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpmeController@selecthelper',
]);


// searches any public helpme
Route::post('helpme/search',[
    'uses' => 'HelpmeController@search',
]);



// select request to help
Route::post('/user/helpme{id}/help',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpmeController@consumehelpme',
]);



/*****************************  HELP SESSION FUNCTIONS FUNCTIONS (INTERRACTION)*/

Route::get('/user/sessions/helpme',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpSessionController@getashelpee',
]);

Route::get('/user/sessions/helpyou',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpSessionController@getashelper',
]);

Route::get('/user/session{id}',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpSessionController@get',
])->where('id', '[0-9]+');

// form with attachements and "message" body
Route::post('/user/session{id}/postmessage',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpSessionController@postmessage',
])->where('id', '[0-9]+');

Route::get('/user/session{id}/head{n}',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpSessionController@head',
])->where(['id' => '[0-9]+', 'n'=>'[0-9]+']);

Route::get('/user/session{id}/allmessages',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpSessionController@allmessages',
])->where('id', '[0-9]+');

Route::post('/user/session{id}/postmessage',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpSessionController@postmessage',
])->where('id', '[0-9]+');

Route::post('/user/session{id}/submitwork',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpSessionController@submitwork',
])->where('id', '[0-9]+');


Route::post('/user/session{id}/acceptwork',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpSessionController@acceptwork',
])->where('id', '[0-9]+');

Route::post('/user/session{id}/review',[
    'middleware' => 'auth.jwt',
    'uses' => 'HelpSessionController@submitreview',
])->where('id', '[0-9]+');

Route::get('/user/session{session_id}/download{message_id}',[
    'middleware' => 'auth.jwt',
    'uses' => 'SessionMessageController@download',
])->where(['session_id' => '[0-9]+', 'message_id'=>'[0-9]+']);