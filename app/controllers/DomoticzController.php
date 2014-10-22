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
	 * Get system informations.
	 * @return string Json formatted system informations.
	 */
	public function system()
	{
		return Response::json(array (
			'id' => 'ISS-Domo Beta Jeedom v0.0.5',
			'apiversion' => 1,
		));
	}

	/**
	* Get Jeedom data.
	* @return string Json formatted system informations.
	*/
	public function getjeedomroom()
	{
		include_once 'jsonrpcClient.class.php';
		
		$jeedomroom = array();

		$jsonrpc = new jsonrpcClient(Config::get('jeedom.jeedom_url'), Config::get('jeedom.api_key'));

		if($jsonrpc->sendRequest('object::all', array()))
		{
			$jeedomroom['result'] = $jsonrpc->getResult();
			//return Response::json($jsonrpc->getResult());
			//return Response::json($jeedomroom);
			return $jeedomroom;
		}
		else
		{
		   echo $jsonrpc->getError();
		}
	}
	
	public function getjeedomdevice($room)
	{
		include_once 'jsonrpcClient.class.php';
		
		$jeedomroom = array();

		$jsonrpc = new jsonrpcClient(Config::get('jeedom.jeedom_url'), Config::get('jeedom.api_key'));

		//if($jsonrpc->sendRequest('eqLogic::all', array()))
		if($jsonrpc->sendRequest('eqLogic::byObjectId', array('object_id' => $room)))
		{
			$jeedomroom['result'] = $jsonrpc->getResult();
			return $jeedomroom;
		}
		else
		{
		   echo $jsonrpc->getError();
		}
	}
	
	public function getjeedomdata($equip)
	{
		include_once 'jsonrpcClient.class.php';
		
		$jeedomroom = array();

		$jsonrpc = new jsonrpcClient(Config::get('jeedom.jeedom_url'), Config::get('jeedom.api_key'));

		//if($jsonrpc->sendRequest('cmd::all', array()))
		if($jsonrpc->sendRequest('cmd::byEqLogicId', array('eqLogic_id' => $equip)))
		{
			$jeedomroom['result'] = $jsonrpc->getResult();
			return $jeedomroom;
			
		}
		else
		{
		   echo $jsonrpc->getError();
		}
	}
	
	public function formatjeedomid($equip)
	{
		$output = array();
	
		$datas = $this->getjeedomdata($equip);
			if(isset($datas['result'])){
					foreach ($datas['result'] as $datadevice) {
						//$output[] = array ('id' => $datadevice['id']);
						$output[] = $datadevice['id'];
					}
			}
		//return Response::json($output);
		return $output;
	}
	
	public function getjeedomdataB($jeedomdataid)
	{
		//$jeedomdataid = array(1752,1753);
		$idd = $this->formatjeedomid($jeedomdataid);
		
		include_once 'jsonrpcClient.class.php';
		
		$jeedomroom = array();
		
		$jsonrpc = new jsonrpcClient(Config::get('jeedom.jeedom_url'), Config::get('jeedom.api_key'));

		if($jsonrpc->sendRequest('cmd::execCmd', array('id' => $idd)))
		{
			$jeedomroom['result'] = array($jsonrpc->getResult());
			return $jeedomroom;
		}
		else
		{
		   echo $jsonrpc->getError();
		}
	}
	
	public function jeedomtest()
	{
		$output = array();
	
		$datas = $this->getjeedomdataB();
			if(isset($datas['result'])){
					foreach ($datas['result'] as $datadevice) {
						//$output[] = array ('id' => $datadevice['id']);
						$output[] = $datadevice['29']['value'];
					}
			}
		//return Response::json($output);
		return $output;
	}
	

	/**
	 * Retrieve the list of the rooms.
	 * @return Json formatted string listing all rooms.
	 */
	public function rooms()
	{
		// convert to app format
		$output = new stdClass();
		$output->rooms = array();
		
		// For Jeedom Rooms
		if(Config::get('hardware.jeedom') == 1){
		$input = $this->getjeedomroom();
			if(isset($input['result'])){
				foreach ($input['result'] as $jeedomrooms) {
				$output->rooms[] = array (
				'id' => $jeedomrooms['id'],
				'name' => $jeedomrooms['name'],
				);
				}	
			}
		}

		return Response::json($output);

	}


	


	/**
	 * Retrieve the list of the available devices.
	 * @return string Json formatted devices list.
	 */
	public function devices()
	{
		$output = new stdClass();
		$output->devices = array();
		
		//Device for Jeedom
		if(Config::get('hardware.jeedom') == 1){
		
		$rooms = $this->getjeedomroom();
		if(isset($rooms['result'])){
			foreach ($rooms['result'] as $room) {
		
		$input = $this->getjeedomdevice($room['id']);
		if(isset($input['result'])){
			foreach ($input['result'] as $device) {
			
				$datas = $this->getjeedomdata($device['id']);
				
				if(isset($datas['result'])){
					foreach ($datas['result'] as $datadevice) {
						
						
							//Modif bug maison hantee...
							switch($datadevice['type']){
								case 'action':
									$output->devices[] = array (
											'id' => $datadevice['id'],
											'name' => $device['name'].'-'.$datadevice['name'],
											'type' => 'DevSwitch',
											'room' => $device['object_id'],
											'params' => array ( array(
												'key' => 'Status',
												'value' => '0',
												),
												),
											);
								break;
								default:
									
									$datasB = $this->getjeedomdataB($device['id']);
									if(isset($datasB['result'])){
										foreach ($datasB['result'] as $datadeviceB) {
											$iddd = $datadevice['id'];
											
											
									$params = self::convertJeedomDeviceStatus($datadevice,$datadeviceB);
							
									$output->devices[] = array (
											'id' => $datadevice['id'],
											'name' => $device['name'].'-'.$datadevice['name'],
											'type' => self::convertJeedomDeviceType($datadevice),
											'room' => $device['object_id'],
											'params' => (null !== $params) ? $params : array()
											);
								break;
											
										}
									}
							}
					
						
					}
				}
			}
		}
		}
		}
		}
		
		return Response::json($output);

	}
	
	/**
	 * Device type for Jeedom
	 */
	private static function convertJeedomDeviceType ($datadevice)
	{
	switch ($datadevice['type']) {
		case 'info':
			switch ($datadevice['subType']) {
				case 'numeric':
					switch ($datadevice['unite']) {
						case '°C':
							$newType = 'DevTemperature';
							break;
						case '%':
							$newType = 'DevHygrometry';
							break;
						case 'Pa':
							$newType = 'DevPressure';
							break;
						case 'km/h':
							$newType = 'DevWind';
							break;
						case 'W':
							$newType = 'DevElectricity';
							break;
						default:
							$newType = 'DevGenericSensor';
							break;
					}
					break;
				default:
					$newType = 'DevGenericSensor';
					break;
			}
		break;
		default:
			$newType = 'DevGenericSensor';
			break;
	}
	return $newType;
	
	}
	
	/**
	 * Param for Jeedom
	 */
	private static function convertJeedomDeviceStatus ($datadevice,$datadeviceB)
	{
	$iddd = $datadevice['id'];

	switch ($datadevice['type']) {
		case 'info':
			switch ($datadevice['subType']) {
				case 'numeric':
					switch ($datadevice['unite']) {
						case '°C':
							$newType = array( array(
										'key' => 'Value',
										'value' => $datadeviceB[$iddd]['value'],
										'unit' => '°C',
										));
							break;
						case '%':
							$newType = array( array(
										'key' => 'Value',
										'value' => $datadeviceB[$iddd]['value'],
										'unit' => '%',
										));
							break;
						case 'Pa':
							$newType = array( array(
											'key' => 'Value',
											'value' => $datadeviceB[$iddd]['value'],
											'unit' => 'mbar',
											));
							break;
						case 'km/h':
							$newType = array( array(
											'key' => 'Speed',
											'value' => $datadeviceB[$iddd]['value'],
											'unit' => 'km/h',
											));
							break;
						case 'W':
							$newType = array( array(
											'key' => 'Watts',
											'value' => $datadeviceB[$iddd]['value'],
											'unit' => 'Watt',
											),
											array(
											'key' => 'ConsoTotal',
											'value' => $datadeviceB[$iddd]['value'],
											'unit' => 'kWh',
											));
							break;
						default:
							$newType = array( array(
											'key' => 'Value',
											'value' => $datadeviceB[$iddd]['value'],
											'unit' => '',
											));
							break;
					}
					break;
				default:
					$newType = array( array(
											'key' => 'Value',
											'value' => $datadeviceB[$iddd]['value'],
											'unit' => '',
											));
					break;
			}
		break;
		default:
			$newType = null;
			break;
	}
	return $newType;
	
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
