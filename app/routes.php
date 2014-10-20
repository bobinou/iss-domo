<?php

Route::get('devices/{deviceId}/action/{actionName}/{actionParam?}', 'DomoticzController@device');
Route::get('devices', 'DomoticzController@devices');
Route::get('rooms', 'DomoticzController@rooms');
//Route::get('action_ret', 'DomoticzController@action_ret');
Route::get('system', 'DomoticzController@system');
Route::get('/', 'DomoticzController@system');
Route::get('movies', 'DomoticzController@getmovies');
Route::get('freebox', 'DomoticzController@getfreebox');
Route::get('xbmcmovies', 'DomoticzController@getxbmcmovies');
Route::get('xbmcsongs', 'DomoticzController@getxbmcsongs');
Route::get('jeedomroom', 'DomoticzController@getjeedomroom');
Route::get('jeedomdevice', 'DomoticzController@getjeedomdevice');
Route::get('jeedomdata', 'DomoticzController@getjeedomdata');
Route::get('jeedomdatab', 'DomoticzController@getjeedomdataB');
Route::get('jeedomformat', 'DomoticzController@formatjeedomid');
Route::get('jeedomtest', 'DomoticzController@jeedomtest');
Route::get('devices/{deviceId}/{paramKey}/histo/{startdate}/{enddate}', 'DomoticzController@device_history');
