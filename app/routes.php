<?php
//Routes for Imperihome
Route::get('devices', 'DomoticzController@devices');
Route::get('rooms', 'DomoticzController@rooms');
Route::get('system', 'DomoticzController@system');
Route::get('/', 'DomoticzController@system');

//Routes test Jeedom
Route::get('jeedomroom', 'DomoticzController@getjeedomroom');
Route::get('jeedomdevice', 'DomoticzController@getjeedomdevice');
Route::get('jeedomdata', 'DomoticzController@getjeedomdata');
Route::get('jeedomdatab', 'DomoticzController@getjeedomdataB');
Route::get('jeedomformat', 'DomoticzController@formatjeedomid');
Route::get('jeedomtest', 'DomoticzController@jeedomtest');
