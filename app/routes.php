<?php

Route::get('devices/{deviceId}/action/{actionName}/{actionParam?}', 'DomoticzController@action');
Route::get('devices', 'DomoticzController@devices');
Route::get('rooms', 'DomoticzController@rooms');
Route::get('system', 'DomoticzController@system');
Route::get('/', 'DomoticzController@system');

Route::get('movies', 'DomoticzController@getmovies');
Route::get('freebox', 'DomoticzController@getfreebox');
Route::get('xbmcmovies', 'DomoticzController@getxbmcmovies');
Route::get('xbmcsongs', 'DomoticzController@getxbmcsongs');
Route::get('jeedomroom', 'DomoticzController@getjeedomroom');

Route::get('devices/{deviceId}/{paramKey}/histo/{startdate}/{enddate}', 'DomoticzController@device_history');
