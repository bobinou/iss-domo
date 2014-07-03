<?php

use \Goutte\Client;

class DomoticzController extends BaseController
{
	/**
	 * Generic Http client.
	 * @var \Goutte\Client 
	 */
	private static $client;

	/**
	 * The domoticz server url.
	 * @var string
	 */
	private $url = 'http://localhost:8180';

	/**
	 * Get system informations.
	 * @return string Json formatted system informations.
	 */
	public function system()
	{
		return Response::json(array (
			'id' => 'Domoticz',
			'apiversion' => 1,
		));
	}

	/**
	 * Retrieve the list of the rooms.
	 * @return Json formatted string listing all rooms.
	 */
	public function rooms()
	{
		//return Response::json(new stdClass());
		$output = array( 'rooms' => array(
				array(
				'id' => '1',
				'name' => 'Salon',
			),
			array(
				'id' => '2',
				'name' => 'Exterieur',
			)),
			);
		return Response::json($output);
		
	}

	/**
	 * Call for an action on the device identified by $deviceId.
	 * @return string Json formated action status.
	 */
	public function device($deviceId, $actionName, $actionParam = null)
	{
		// file_put_contents('/var/www/laravel/export.log', $deviceId.'-'.$actionName.'-'.$actionParam."\n");
		$actionName = 'setStatus' == $actionName ? 'switchlight' : $actionName;
		$actionParam = '0' == $actionParam ? 'Off' : 'On';
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url($this->url, "json.htm?type=command&param={$actionName}&idx={$deviceId}}&switchcmd=$actionParam"));
		$response = $request->send();
		$input = $response->json();
		
		// convert to app format
		$output = array('success' => ('OK' === $input['status'] ? true : false), 'errormsg' => ('ERR' === $input['status'] ? 'An error occured' : ''));

		return Response::json($output);
	}

	/**
	 * Retrieve the list of the available devices.
	 * @return string Json formatted devices list.
	 */
	public function devices()
	{
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url($this->url, 'json.htm?type=devices&used=true'));
		$response = $request->send();
		$input = $response->json();
		
		// convert to app format
		$output = new stdClass();
		$output->devices = array();
		
		foreach ($input['result'] as $device) {
	//var_dump($device); die; 
	$params = self::convertDeviceStatus($device);
	$output->devices[] = array (
				'id' => $device['idx'],
				'name' => $device['Name'],
				'type' => self::convertDeviceType($device),
				'room' => self::convertDeviceRooms($device['SubType']),
				'params' => (null !== $params) ? array($params) : array(),					
				);
		}
		
		return Response::json($output);
	}

	
	/**
	 * 
	 * Available values: DevSwitch/DevDimmer/DevCamera/etc...
	 *
	 */
	private static function convertDeviceType ($device)
	{
		switch ($device['Type']) {
			case (0 === strpos($device['Type'], 'Lighting')):
				$newType = 'DevSwitch';
				break;
			case (0 === strpos($device['Type'], 'Temp')):
				$newType = 'DevTemperature';
				break;
			case 'Wind':
				$newType = 'DevWind';
				break;
			case 'Rain':
				$newType = 'DevRain';
				break;
			case 'General':
				$newType = 'DevGenericSensor';
				break;
			case 'UV':
				$newType = 'DevUV';
				break;
			case 'Energy':
				$newType = 'DevElectricity';
				break;
			default:
				$newType = 'DevGenericSensor';
				break;
		}
		
		return $newType;
	}
	
	
private static function convertDeviceRooms ($type)
	{
		switch ($type) {
			case 'X10':
				$newRooms = '1';
				break;
			case 'Blyss':
				$newRooms = '1';
				break;
			case 'THR128/138, THC138':
				$newRooms = '1';
				break;
			case 'Percentage':
				$newRooms = '1';
				break;
			case 'System':
				$newRooms = '1';
				break;
			default:
				$newRooms = '2';
				break;
		}
		
		return $newRooms;
	}
	
private static function convertDeviceStatus ($device)
	{
		switch ($device['Type']) {
			case (0 === strpos($device['Type'], 'Lighting')):
					$output = array(
						'key' => 'Status',
						'value' => 'Off' == $device['Status'] ? '0' : '1',
						);
			break;
			case (0 === strpos($device['Type'], 'Temp')):
				$output = array(
						'key' => 'Value',
						'value' => $device['Temp'],
						'unit' => '°C',
						);
				break;
			case 'General':
				$output = array(
						'key' => 'Value',
						'value' => $device['Data'],
						'unit' => '',
						);
				break;
			case 'UV':
				$output = array(
						'key' => 'Value',
						'value' => $device['UVI'],
						'unit' => 'UVI',
						);
				break;
			case 'Rain':
				$output = array(
						'key' => 'Value',
						'value' => $device['Rain'],
						'unit' => 'mm',
						);
				break;
			case 'Wind':
				$output = 	array(
						'key' => 'Speed',
						'value' => $device['Speed'],
						'unit' => 'km/h',
						);
				break;
			default:
					$output = null;
			break;
		}
		
		return $output;
		
	}
	
	/**
	 * Get the http client instance.
	 * @return \Goutte\Client
	 */
	private function getClient()
	{
		if (null === self::$client) {
			self::$client = new Client();
		}

		return self::$client;
	}
}
