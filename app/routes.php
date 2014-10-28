<?php
//Routes for Imperihome
Route::get('devices/{deviceId}/action/{actionName}/{actionParam?}', 'DomoticzController@device');
Route::get('devices', 'DomoticzController@devices');
Route::get('rooms', 'DomoticzController@rooms');
Route::get('system', 'DomoticzController@system');
Route::get('/', 'DomoticzController@system');

//Routes test Jeedom
Route::get('jeedomroom', 'DomoticzController@getjeedomrooms');
Route::get('jeedomfull', 'DomoticzController@getjeedomfull');
