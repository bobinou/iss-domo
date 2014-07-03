<?php

Route::get('devices/{deviceId}/action/{actionName}/{actionParam?}', 'DomoticzController@device');
Route::get('devices', 'DomoticzController@devices');
Route::get('rooms', 'DomoticzController@rooms');
//Route::get('action_ret', 'DomoticzController@action_ret');
Route::get('system', 'DomoticzController@system');
Route::get('/', 'DomoticzController@system');
Route::get('movies', 'DomoticzController@getmovies');
Route::get('freebox', 'DomoticzController@getfreebox');