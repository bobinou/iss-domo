<?php
/**
 * Routes for Imperihome
**/
//action
Route::get('devices/{deviceId}/action/{actionName}/{actionParam?}', 'DomoticzController@action');
//devices
Route::get('devices', 'DomoticzController@devices');
//rooms
Route::get('rooms', 'DomoticzController@rooms');
//system
Route::get('system', 'DomoticzController@system');
Route::get('/', 'DomoticzController@system');
//history
Route::get('devices/{deviceId}/{paramKey}/histo/{startdate}/{enddate}', 'DomoticzController@device_history');

//Routes for other hardware
Route::get('movies', 'DomoticzController@getmovies');
Route::get('freebox', 'DomoticzController@getfreebox');
Route::get('xbmcmovies', 'DomoticzController@getxbmcmovies');
Route::get('xbmcsongs', 'DomoticzController@getxbmcsongs');

//Routes test Jeedom
Route::get('jeedomroom', 'DomoticzController@getjeedomrooms');
Route::get('jeedomfull', 'DomoticzController@getjeedomfull');
