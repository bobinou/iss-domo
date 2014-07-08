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
			'id' => 'ISS-Domo',
			'apiversion' => 1,
		));
	}

	public function getxbmcmovies()
	{
		if(Config::get('hardware.xbmc') == 1 AND Config::get('xbmc.xbmc_movies') == 1 AND Config::get('xbmc.xbmc_status') == 1){
		$client = $this->getClient()->getClient();
		$request = $client->createRequest('GET', Config::get('xbmc.xbmc_url'));
		$q = $request->getQuery();

		$params = Config::get('xbmc.args-movies');

		foreach ($params as $k => $v) {
			$q->set($k, $v);
		}

		$response = $request->send();
		return $response->json();
		}
	}
	
	public function getxbmcsongs()
	{
		if(Config::get('hardware.xbmc') == 1 AND Config::get('xbmc.xbmc_songs') == 1 AND Config::get('xbmc.xbmc_status') == 1){
		$client = $this->getClient()->getClient();
		$request = $client->createRequest('GET', Config::get('xbmc.xbmc_url'));
		$q = $request->getQuery();

		$params = Config::get('xbmc.args-songs');

		foreach ($params as $k => $v) {
			$q->set($k, $v);
		}

		$response = $request->send();
		return $response->json();
		}
	}
	
	/**public function getxbmcplaying()
	{
		if(Config::get('hardware.xbmc') == 1 AND Config::get('xbmc.xbmc_status') == 1){
		$client = $this->getClient()->getClient();
		$request = $client->createRequest('GET', Config::get('xbmc.xbmc_url'));
		$q = $request->getQuery();

		$params = Config::get('xbmc.what_is_playing');

		foreach ($params as $k => $v) {
			$q->set($k, $v);
		}

		$response = $request->send();
		return $response->json();
		}
	}**/

	public function getMovies()
	{
		if(Config::get('hardware.nas') == 1){
			return Response::json($this->_getMovies());
		}
	}

	private function _getMovies()
	{
		if(Config::get('hardware.nas') == 1){
		$movies = array();
		$files = glob(Config::get('nas.movies_dir').'/*.{avi,mkv}', GLOB_BRACE);
		foreach ($files as $i => $filename) {
			$movies[] = array('id' => $i, 'name' => basename($filename));
		}

		return $movies;
		}
	}

	public function getfreebox()
	{
		$data = array();
		if(Config::get('hardware.freebox_server') == 1){
			$api = new \Freebox\Api();
			$data['config'] = $api->config();
		}

		//return Response::json($data);
		return $data;
	}

	public function getroom()
	{
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url(Config::get('iss-domo.domoticz_url'), 'json.htm?type=plans'));
		$response = $request->send();
		return $response->json();
	}

	public function getdevicenorooms()
	{
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url(Config::get('iss-domo.domoticz_url'), 'json.htm?type=devices&used=true'));
		$response = $request->send();
		return $response->json();
	}

	public function getdevice($roomid)
	{
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url(Config::get('iss-domo.domoticz_url'), "json.htm?type=devices&used=true&plan={$roomid}"));
		$response = $request->send();
		return $response->json();
	}

	public function getscene()
	{
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url(Config::get('iss-domo.domoticz_url'), 'json.htm?type=scenes'));
		$response = $request->send();
		return $response->json();
	}

	public function getcamera()
	{
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url(Config::get('iss-domo.domoticz_url'), 'json.htm?type=cameras'));
		$response = $request->send();
		return $response->json();
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
		
		if(Config::get('hardware.domoticz') == 1){
		$input = $this->getroom();
		if(isset($input['result'])){

		foreach ($input['result'] as $rooms) {
		$output->rooms[] = array (
				'id' => $rooms['idx'],
				'name' => $rooms['Name'],
				);
		}

		}
		else{
		$output->rooms[] = array (
				'id' => 'A',
				'name' => 'bsss',
				);
		}
		}
		
		// Add Movies Rooms
		if(Config::get('hardware.xbmc') == 1 AND Config::get('xbmc.xbmc_movies') == 1){
		$output->rooms[] = array (
				'id' => '999',
				'name' => 'Films',
				);
		}
		
		// Add Songs Rooms
		if(Config::get('hardware.xbmc') == 1 AND Config::get('xbmc.xbmc_songs') == 1){
		$output->rooms[] = array (
				'id' => '998',
				'name' => 'Musiques',
				);
		}

		return Response::json($output);

	}

	/**
	 * Call for an action on the device identified by $deviceId.
	 * @return string Json formated action status.
	 */
	public function device($deviceId, $actionName, $actionParam = null)
	{
		// file_put_contents('/var/www/laravel/export.log', $deviceId.'-'.$actionName.'-'.$actionParam."\n");
		//*** For switchs, Dimmer, Lock ***
		if($actionName == 'setStatus'){
			if(strpos($deviceId, 'noroom')){
				$arraydeviceId = explode("-", $deviceId);
				$deviceId = $arraydeviceId[0];
			}
		$actionName = 'setStatus' == $actionName ? 'switchlight' : $actionName;
		$actionParam = '0' == $actionParam ? 'Off' : 'On';
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url(Config::get('iss-domo.domoticz_url'), "json.htm?type=command&param={$actionName}&idx={$deviceId}}&switchcmd=$actionParam"));
		$response = $request->send();
		$input = $response->json();

		// convert to app format
		$output = array('success' => ('OK' === $input['status'] ? true : false), 'errormsg' => ('ERR' === $input['status'] ? 'An error occured' : ''));

		return Response::json($output);
		}
		
		//*** For MOVIES from XBMC ***
		// Launch Movie to XBMC where type command launchScene
		if($actionName == 'launchScene' AND strpos($deviceId, 'xbmove')){
		
		//What is the id of the movie
		$arraydeviceId = explode("-", $deviceId);
		$deviceId = $arraydeviceId[0];

		//Clear Xbmc playlist (id=0)
		$client = $this->getClient()->getClient();
		$request = $client->createRequest('GET', Config::get('xbmc.xbmc_url'));
		$q = $request->getQuery();
		$params = Config::get('xbmc.clear_playlist');
		foreach ($params as $k => $v) {
			$q->set($k, $v);
		}
		$response = $request->send();
		
		//Add movie ($deviceId) to Playlist (id=0)
		$client = $this->getClient()->getClient();
		$request = $client->createRequest('GET', Config::get('xbmc.xbmc_url'));
		$q = $request->getQuery();
		$params = array(
        'request' => '{"jsonrpc":"2.0","id":1,"method":"Playlist.Add","params":{"playlistid":0,"item":{"movieid":'.$deviceId.'}}}'
		);
		foreach ($params as $k => $v) {
			$q->set($k, $v);
		}
		$response = $request->send();
		
		//Play Xbmc playlist (id=0)
		$client = $this->getClient()->getClient();
		$request = $client->createRequest('GET', Config::get('xbmc.xbmc_url'));
		$q = $request->getQuery();
		$params = Config::get('xbmc.play_playlist');
		foreach ($params as $k => $v) {
			$q->set($k, $v);
		}
		$response = $request->send();
		
		}
		
		
		//*** For SONGS from XBMC ***
		// Launch Songs to XBMC where type command launchScene
		if($actionName == 'launchScene' AND strpos($deviceId, 'xbsong')){
		
		//What is the id of the song
		$arraydeviceId = explode("-", $deviceId);
		$deviceId = $arraydeviceId[0];

		//Clear Xbmc playlist (id=0)
		$client = $this->getClient()->getClient();
		$request = $client->createRequest('GET', Config::get('xbmc.xbmc_url'));
		$q = $request->getQuery();
		$params = Config::get('xbmc.clear_playlist');
		foreach ($params as $k => $v) {
			$q->set($k, $v);
		}
		$response = $request->send();
		
		//Add song ($deviceId) to Playlist (id=0)
		$client = $this->getClient()->getClient();
		$request = $client->createRequest('GET', Config::get('xbmc.xbmc_url'));
		$q = $request->getQuery();
		$params = array(
        'request' => '{"jsonrpc":"2.0","id":1,"method":"Playlist.Add","params":{"playlistid":0,"item":{"songid":'.$deviceId.'}}}'
		);
		foreach ($params as $k => $v) {
			$q->set($k, $v);
		}
		$response = $request->send();
		
		//Play Xbmc playlist (id=0)
		$client = $this->getClient()->getClient();
		$request = $client->createRequest('GET', Config::get('xbmc.xbmc_url'));
		$q = $request->getQuery();
		$params = Config::get('xbmc.play_playlist');
		foreach ($params as $k => $v) {
			$q->set($k, $v);
		}
		$response = $request->send();
		
		}
		
		

		//*** For movies from NAS ***
		// TEST NOT IMPEMENTED
		/**if(Config::get('hardware.nas') == 1){
		if($actionName == 'launchScene'){
		$arraydeviceId = explode("-", $deviceId);
		$deviceId = $arraydeviceId[0];
		// Sequence pour se rendre depuis freebox player dans le repertoire de notre nas
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=home'));
		$response = $request->send();
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=red'));
		$response = $request->send();
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=right&long=true'));
		$response = $request->send();
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=ok'));
		$response = $request->send();
		sleep(3);
		//On est dans les disques
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=down'));
		$response = $request->send();
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=ok'));
		$response = $request->send();
		sleep(1);
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=down'));
		$response = $request->send();
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=down'));
		$response = $request->send();
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=ok'));
		$response = $request->send();
		sleep(1);
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=down&long=true'));
		$response = $request->send();
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=up'));
		$response = $request->send();
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=ok'));
		$response = $request->send();
		sleep(5);
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=down'));
		$response = $request->send();
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=down'));
		$response = $request->send();
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=ok'));
		$response = $request->send();
		sleep(5);
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=ok'));
		$response = $request->send();
		//Nous sommes dans le repertoire de notre nas
		sleep(5);
		$nbr_file = 1;
		while ($nbr_file <= $deviceId)
			{
    		$client = $this->getClient();
			$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=down'));
			$response = $request->send();
    		$nbr_file++;
    		sleep(0.5);
			}
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url('http://hd1.freebox.fr/pub', 'remote_control?code='.Config::get('freebox.remote_code').'&key=ok'));
		$response = $request->send();

		}
		}**/

	}

	/**
	 * Retrieve the list of the available devices.
	 * @return string Json formatted devices list.
	 */
	public function devices()
	{
		$output = new stdClass();
		$output->devices = array();
		
		if(Config::get('hardware.domoticz') == 1){
		$input = $this->getroom();
		if(isset($input['result'])){
		foreach ($input['result'] as $rooms) {

			$devices = $this->getdevice($rooms['idx']);

			if(isset($devices['result'])){

				foreach ($devices['result'] as $device) {

					$params = self::convertDeviceStatus($device);
					$output->devices[] = array (
						'id' => $device['idx'],
						'name' => $device['Name'],
						'type' => self::convertDeviceType($device),
						'room' => $rooms['idx'],
						//'params' => (null !== $params) ? array($params) : array(),
						'params' => (null !== $params) ? $params : array(),
						);
				}
			}
		}
		}
		//Add Cameras
		$cameras = $this->getcamera();
		if(isset($cameras['result'])){
			foreach ($cameras['result'] as $cam) {
				$output->devices[] = array(
					'id' => 'c'.$cam['idx'],
					'name' => $cam['Name'],
					'type' => 'DevCamera',
					'room' => '',
					'params' => array( array(
						'key' => 'localjpegurl',
						'value' => "http://".$cam['Address'].':'.$cam['Port'].'/'.$cam['ImageURL'],
						),
						array(
						'key' => 'remotemjpegurl',
						'value' => "http://".$cam['Address'].':'.$cam['Port'].'/'.Config::get('iss-domo.url_cam_video'),
						),
						array(
						'key' => 'Login',
						'value' => $cam['Username'],
						),
						array(
                        'key' => 'Password',
                        'value' => $cam['Password'],
                        ),
						),
					);
			}
		}

		//Add Scenes
		$scenes = $this->getscene();
		if(isset($scenes['result'])){
			foreach ($scenes['result'] as $sce) {
				$output->devices[] = array(
					'id' => 's'.$sce['idx'],
					'name' => $sce['Name'],
					'type' => 'DevScene',
					'room' => '',
					'params' => array( array(
						'key' => 'LastRun',
						'value' => $sce['LastUpdate'],
						),
						),
					);
			}
		}

		//Add no Rooms devices
		$devicenorooms = $this->getdevicenorooms();
		if(isset($devicenorooms['result'])){
			foreach ($devicenorooms['result'] as $dnr) {
				$params = self::convertDeviceStatus($dnr);
				$output->devices[] = array(
					'id' => $dnr['idx'].'-noroom',
					'name' => $dnr['Name'],
					'type' => self::convertDeviceType($dnr),
					'room' => '',
					'params' => (null !== $params) ? $params : array(),
					);

			}
		}
		}
		
		//Add Freebox server infos
		if(Config::get('hardware.freebox_server') == 1 AND Config::get('hardware.domoticz') == 0){
		$freeboxserver = $this->getfreebox();
		if(isset($freeboxserver['config'])){
			foreach ($freeboxserver['config'] as $freeserv) {
				//Ventilateur
				$output->devices[] = array(
					'id' => 'free1',
					'name' => 'Freebox Ventilateur',
					'type' => 'DevGenericSensor',
					'room' => '',
					'params' => array( array(
						'key' => 'Value',
						'value' => $freeserv['fan_rpm'],
						'unit' => 'rpm',
						),
						),
					);
				//temp_sw
				$output->devices[] = array(
					'id' => 'free2',
					'name' => 'Freebox Temp SW',
					'type' => 'DevTemperature',
					'room' => '',
					'params' => array( array(
						'key' => 'Value',
						'value' => $freeserv['temp_sw'],
						'unit' => '°C',
						),
						),
					);
				//temp_cpub
				$output->devices[] = array(
					'id' => 'free3',
					'name' => 'Freebox Temp CPU B',
					'type' => 'DevTemperature',
					'room' => '',
					'params' => array( array(
						'key' => 'Value',
						'value' => $freeserv['temp_cpub'],
						'unit' => '°C',
						),
						),
					);
				//temp_cpum
				$output->devices[] = array(
					'id' => 'free4',
					'name' => 'Freebox Temp CPU M',
					'type' => 'DevTemperature',
					'room' => '',
					'params' => array( array(
						'key' => 'Value',
						'value' => $freeserv['temp_cpum'],
						'unit' => '°C',
						),
						),
					);
				//Firmware
				$output->devices[] = array(
					'id' => 'free5',
					'name' => 'Freebox Firmware',
					'type' => 'DevGenericSensor',
					'room' => '',
					'params' => array( array(
						'key' => 'Value',
						'value' => $freeserv['firmware_version'],
						'unit' => '',
						),
						),
					);
				//Authentification
				$output->devices[] = array(
					'id' => 'free6',
					'name' => 'Freebox is on Wan',
					'type' => 'DevGenericSensor',
					'room' => '',
					'params' => array( array(
						'key' => 'Value',
						'value' => $freeserv['box_authenticated'],
						'unit' => '',
						),
						),
					);
				//Uptime
				$output->devices[] = array(
					'id' => 'free7',
					'name' => 'Freebox Uptime',
					'type' => 'DevGenericSensor',
					'room' => '',
					'params' => array( array(
						'key' => 'Value',
						'value' => $freeserv['uptime_val'],
						'unit' => '',
						),
						),
					);

			}
		}
		}

		//Add movies from NAS
		if(Config::get('hardware.nas') == 1){
		$films = $this->_getMovies();
			foreach ($films as $ff) {
				$output->devices[] = array(
					'id' => $ff['id'].'-mv',
					'name' => $ff['name'],
					'type' => 'DevScene',
					'room' => '999',
					'params' => array(),
					);

			}
		}
		
		//Add movies from XBMC
		if(Config::get('hardware.xbmc') == 1 AND Config::get('xbmc.xbmc_movies') == 1 AND Config::get('xbmc.xbmc_status') == 1){
		$xbmcfilms = $this->getxbmcmovies();
			foreach ($xbmcfilms['result']['movies'] as $xfilms) {
				$output->devices[] = array(
					'id' => $xfilms['movieid'].'-xbmove',
					'name' => $xfilms['label'],
					'type' => 'DevScene',
					'room' => '999',
					'params' => array(),
					);

			}
		}
		
		//Add songs from XBMC
		if(Config::get('hardware.xbmc') == 1 AND Config::get('xbmc.xbmc_songs') == 1 AND Config::get('xbmc.xbmc_status') == 1){
		$xbmcmusiques = $this->getxbmcsongs();
			foreach ($xbmcmusiques['result']['songs'] as $xmusiques) {
				$output->devices[] = array(
					'id' => $xmusiques['songid'].'-xbsong',
					'name' => $xmusiques['label'],
					'type' => 'DevScene',
					'room' => '998',
					'params' => array(),
					);

			}
		}
		
		//Add Current Play in Xbmc
		/**if(Config::get('hardware.xbmc') == 1 AND Config::get('xbmc.xbmc_status') == 1){
		$xbmcplaying = $this->getxbmcplaying();
		if(isset($xbmcplaying['result'])){
			foreach ($xbmcplaying['result'] as $playing) {
				$params = self::convertXBMCStatus($playing);
				$output->devices[] = array(
					'id' => 'playing_xbmc',
					'name' => 'Lecture en cours',
					'type' => 'DevGenericSensor',
					'room' => '999',
					'params' => (null !== $params) ? $params : array(),
					);

			}
		}
		}**/

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
			// All switchs types
			case (0 === strpos($device['Type'], 'Lighting')):
				switch($device['SwitchType']) {
					case 'On/Off':
						$newType = 'DevSwitch';
						break;
					case 'Push On Button':
						$newType = 'DevSwitch';
						break;
					case 'Push Off Button':
						$newType = 'DevSwitch';
						break;
					case 'Smoke Detector':
						$newType = 'DevSmoke';
						break;
					case 'Door Lock':
						$newType = 'DevDoor';
						break;
					case 'Motion Sensor':
						$newType = 'DevMotion';
						break;
					case 'Blinds Inverted':
						$newType = 'DevShutter';
						break;
					case 'Blinds':
						$newType = 'DevShutter';
						break;
					case 'Blinds Percentage':
						$newType = 'DevShutter';
						break;
					case 'Dimmer':
						$newType = 'DevDimmer';
						break;
					default:
						$newType = 'DevSwitch';
					break;
				}
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
			case 'Usage':
				$newType = 'DevElectricity';
				break;
			case 'Current/Energy':
				$newType = 'DevElectricity';
				break;
			default:
				$newType = 'DevGenericSensor';
				break;
		}

		return $newType;
	}

/**private static function convertXBMCStatus ($playing)
	{
		if(isset($xbmcplaying['result'])){
			$output = array( array(
						'key' => 'Value',
						'value' => $playing['label'],
						'unit' => '',
						));
		}
		else {
			$output = null;
		}
	}**/
	
private static function convertDeviceStatus ($device)
	{
		switch ($device['Type']) {
			case (0 === strpos($device['Type'], 'Lighting')):
				switch($device['SwitchType']) {
					case 'On/Off':
						$output = array( array(
							'key' => 'Status',
							'value' => 'Off' == $device['Status'] ? '0' : '1',
						));
					break;
					case 'Push On Button':
						$output = array( array(
							'key' => 'Status',
							'value' => 'Off' == $device['Status'] ? '0' : '1',
						));
					break;
					case 'Push Off Button':
						$output = array( array(
							'key' => 'Status',
							'value' => 'Off' == $device['Status'] ? '0' : '1',
						));
					break;
					case 'Smoke Detector':
						$output = array( array(
							'key' => 'Tripped',
							'value' => 'Off' == $device['Status'] ? '0' : '1',
						),
						array(
							'key' => 'Armed',
							'value' => '1',
						),
						array(
							'key' => 'Ackable',
							'value' => '1',
						),
						array(
							'key' => 'Armable',
							'value' => '1',
						));
					break;
					case 'Door Lock':
						$output = array( array(
							'key' => 'Tripped',
							'value' => 'Off' == $device['Status'] ? '0' : '1',
						),
						array(
							'key' => 'Armed',
							'value' => '1',
						),
						array(
							'key' => 'Ackable',
							'value' => '1',
						),
						array(
							'key' => 'Armable',
							'value' => '1',
						));
					break;
					case 'Motion Sensor':
						$output = array( array(
							'key' => 'Tripped',
							'value' => 'Off' == $device['Status'] ? '0' : '1',
						),
						array(
							'key' => 'Armed',
							'value' => '1',
						),
						array(
							'key' => 'Ackable',
							'value' => '1',
						),
						array(
							'key' => 'Armable',
							'value' => '1',
						));
					break;
					case 'Blinds Inverted':
						$output = array( array(
							'key' => 'Level',
							'value' => $device['Level'],
						),
						array(
							'key' => 'stopable',
							'value' => '0',
						),
						array(
							'key' => 'pulseable',
							'value' => '0',
						));
					break;
					case 'Blinds':
						$output = array( array(
							'key' => 'Level',
							'value' => $device['Level'],
						),
						array(
							'key' => 'stopable',
							'value' => '0',
						),
						array(
							'key' => 'pulseable',
							'value' => '0',
						));
					break;
					case 'Blinds Percentage':
						$output = array( array(
							'key' => 'Level',
							'value' => $device['Level'],
						),
						array(
							'key' => 'stopable',
							'value' => '1',
						),
						array(
							'key' => 'pulseable',
							'value' => '0',
						));
					break;
					break;
					case 'Dimmer':
						$output = array( array(
							'key' => 'Level',
							'value' => $device['Level'],
						),
						array(
							'key' => 'Status',
							'value' => 'Off' == $device['Status'] ? '0' : '1',
						));
					break;
					default:
						$output = array( array(
							'key' => 'Status',
							'value' => 'Off' == $device['Status'] ? '0' : '1',
						)
					);
				}
			break;
			case (0 === strpos($device['Type'], 'Temp')):
				$output = array( array(
						'key' => 'Value',
						'value' => $device['Temp'],
						'unit' => '°C',
						));
				break;
			case 'General':
				switch($device['Name']) {
					case (0 === strpos($device['Name'], 'Freebox')):
						$dataDevice = explode(".", $device['Data']);
						$ddDevice = $dataDevice[0];
						$output = array( array(
							'key' => 'Value',
							'value' => $ddDevice,
							'unit' => 'rpm',
						));
					break;
				default:
				$output = array( array(
						'key' => 'Value',
						'value' => $device['Data'],
						'unit' => '',
						));
						break;
				}
				break;
			case 'UV':
				$output = array( array(
						'key' => 'Value',
						'value' => $device['UVI'],
						'unit' => 'UVI',
						));
				break;
			case 'Rain':
				$output = array( array(
						'key' => 'Value',
						'value' => $device['RainRate'],
						'unit' => 'mm/h',
						),
						array(
						'key' => 'Accumulation',
						'value' => $device['Rain'],
						'unit' => 'mm',
						));
				break;
			case 'Wind':
				$output = 	array( array(
						'key' => 'Speed',
						'value' => $device['Speed'],
						'unit' => 'km/h',
						));
				break;
			case 'Usage':
				$output = 	array( array(
						'key' => 'Watts',
						'value' => $device['Data'],
						'unit' => '',
						));
				break;
			case 'Current/Energy':
				$output = 	array( array(
						'key' => 'Watts',
						'value' => $device['Data'],
						'unit' => '',
						));
				break;
			case 'Energy':
				$output = 	array( array(
						'key' => 'Watts',
						'value' => $device['Usage'],
						'unit' => '',
						),
						array(
						'key' => 'ConsoTotal',
						'value' => $device['Data'],
						'unit' => '',
						));
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
			//var_dump($device); die;
