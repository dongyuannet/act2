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

Route::get('/{uid?}/{aid?}', 'HomeController@pre')->where(['uid'=>'[0-9]+'])->where(['aid'=>'[0-9]+']);
Route::get('/index/{uid?}/{aid?}', 'HomeController@index')->where(['uid'=>'[0-9]+'])->where(['aid'=>'[0-9]+']);

// 点赞
Route::post('/dianzan', 'HomeController@dianzan');

Route::get('/jsToken', 'HomeController@jsToken');
Route::get('/get_jsapi_ticket', 'HomeController@get_jsapi_ticket');

Route::post('/addSharefen', 'HomeController@addSharefen');

Route::post('/formsign', 'HomeController@formsign');
Route::post('/sendYzm', 'HomeController@sendYzm');
Route::get('/morebm', 'HomeController@moreBm');
Route::get('/morezhulip', 'HomeController@moreZhulip');
Route::get('/morescorep', 'HomeController@moreScorep');


