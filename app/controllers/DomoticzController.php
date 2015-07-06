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
			'id' => 'ISS-Domo v3.2.0',
			'apiversion' => 1,
		));
	}

   /**
   *GetJeedomFULLdata.
   *@returnstringJsonformattedsysteminformations.
   */
   public function getjeedomfull()
   {
      include_once 'jsonrpcClient.class.php';

      $jeedomfull = array();

      $jsonrpc = new jsonrpcClient(Config::get('jeedom.jeedom_url'),Config::get('jeedom.api_key'));

      if($jsonrpc -> sendRequest('object::full',array()))
      {
         $jeedomfull['result'] = $jsonrpc->getResult();
         return $jeedomfull;
      }
      else
      {
         echo $jsonrpc -> getError();
      }
   }
   
      /**
   *getjeedomscene.
   *@returnstringJsonformattedsysteminformations.
   */
   public function getjeedomscene()
   {
      include_once 'jsonrpcClient.class.php';

      $jeedomscene = array();

      $jsonrpc = new jsonrpcClient(Config::get('jeedom.jeedom_url'),Config::get('jeedom.api_key'));

      if($jsonrpc -> sendRequest('scenario::all',array()))
      {
         $jeedomscene['result'] = $jsonrpc->getResult();
         return $jeedomscene;
      }
      else
      {
         echo $jsonrpc -> getError();
      }
   }
   
   /**
   *getjeedomhistory.
   *@returnstringJsonformattedsysteminformations.
   */
   public function getjeedomhistory($deviceId, $startdate, $enddate)
   {
		$startdatejeedom = date('Y-m-d H:i:s', ($startdate / 1000));
		$enddatejeedom = date('Y-m-d H:i:s', ($enddate / 1000));
   
      include_once 'jsonrpcClient.class.php';

      $jeedomhistory = array();

      $jsonrpc = new jsonrpcClient(Config::get('jeedom.jeedom_url'),Config::get('jeedom.api_key'));

      if($jsonrpc -> sendRequest('cmd::getHistory',array('id' => $deviceId, 'startTime' => $startdatejeedom, 'endTime' => $enddatejeedom)))
      {
         $jeedomhistory['result'] = $jsonrpc->getResult();
         return $jeedomhistory;
      }
      else
      {
         echo $jsonrpc -> getError();
      }
   }

   /**
   *getjeedomrooms. Retreive Rooms for Jeedom
   *@returnstringJsonformattedsysteminformations.
   */
   public function getjeedomrooms()
   {
      include_once 'jsonrpcClient.class.php';

      $jeedomrooms = array();

      $jsonrpc = new jsonrpcClient(Config::get('jeedom.jeedom_url'),Config::get('jeedom.api_key'));

      if($jsonrpc -> sendRequest('object::all',array()))
      {
         $jeedomrooms['result'] = $jsonrpc->getResult();
         return $jeedomrooms;
      }
      else
      {
         echo $jsonrpc -> getError();
      }
   }

	// Private function search  for Jeedom 
   private static function recherche($fulldata, $infos, $cmd, $test)
   {
      $autre = null;
      foreach ($fulldata['result'] as $data) {
         foreach ($data['eqLogics'] as $equipment) {
            foreach ($equipment['cmds'] as $command) {
               if ($cmd == null){
                  if ($command['id'] == $infos){
                     $id_retour = $command[$test];
                     if(isset($equipment['configuration']['device'])){
                        $autre = $equipment['configuration']['device'];
                     }
                     else {
                        $autre = null;
                     }
                  }
               }
               else {
                  if ($command['eqLogic_id'] == $infos and $command['name'] == $cmd){
                     $id_retour = $command[$test];
                  }
               }
            }
         }
      }
      return array($id_retour,$autre);
   }
	
	
	/**
	* Get XBMC data.
	* @return string Json formatted system informations.
	*/
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
	
	/**
	* Get NAS data.
	* @return string Json formatted system informations.
	*/
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

	/**
	* Get Freebox data.
	* @return string Json formatted system informations.
	*/
	public function getfreebox()
	{
		$data = array();
		if(Config::get('hardware.freebox_server') == 1){
			$api = new \Freebox\Api();
			$data['config'] = $api->config();
		}

		return $data;
	}

	/**
	* Get Domoticz data.
	* @return string Json formatted system informations.
	*/
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
		
      //ForJeedomRooms
      if (Config::get('hardware.jeedom') == 1){
         $input = $this -> getjeedomrooms();
         if (isset ($input['result'])){
            foreach ($input['result'] as $jeedomrooms){
               $output -> rooms[] = array(
               'id' => $jeedomrooms['id'],
               'name' => $jeedomrooms['name'],
               );
            }
         }
      }
		
		// For Domoticz Rooms
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
		
		// Add Movies Rooms for XBMC
		if(Config::get('hardware.xbmc') == 1 AND Config::get('xbmc.xbmc_movies') == 1){
		$output->rooms[] = array (
				'id' => '999',
				'name' => 'Films',
				);
		}
		
		// Add Songs Rooms for XBMC
		if(Config::get('hardware.xbmc') == 1 AND Config::get('xbmc.xbmc_songs') == 1){
		$output->rooms[] = array (
				'id' => '998',
				'name' => 'Musiques',
				);
		}

		return Response::json($output);

	}

	/**
	* Function to show history
	*/
	public function device_history($deviceId, $paramKey, $startdate, $enddate)
	{
		//file_put_contents('/home/pi/iss-domo-beta/export.log', $deviceId.'-'.$paramKey.'-'.$startdate.'-'.$enddate."\n");
		
		//history for Domoticz
		if(Config::get('hardware.domoticz') == 1){
		$db = new SQLite3(Config::get('iss-domo.path_to_db'));
		$result = $db->query('SELECT * FROM domoticz_history WHERE idx = "'.$deviceId.'" AND key = "'.strtolower($paramKey).'" AND date >= "'.$startdate.'" AND date <= "'.$enddate.'"');
		$row = array();
		$i = 0;
		while($res = $result->fetchArray(SQLITE3_ASSOC)){ 
             if(!isset($res['id'])) continue; 
			  $row[$i]['date'] = $res['date'];
			  $row[$i]['value'] = $res['data'];
              $i++;
         } 
		$ckoi = json_encode(array('values'=>$row));
		print_r($ckoi);
		}
		
		//history for Jeedom
		if(Config::get('hardware.jeedom') == 1){
			$history = $this->getjeedomhistory($deviceId, $startdate, $enddate);
			if(isset($history['result'])){
				foreach ($history['result'] as $histo) {
					$milliseconds = round(strtotime($histo['datetime']) * 1000);
					$output->values[] = array (
                                       'date' => $milliseconds,
                                       'value' => $histo['value'],
                                       );				   
				}
			}
			return Response::json($output);
		}
	}
	
	/**
	 * Call for an action on the device identified by $deviceId.
	 * @return string Json formated action status.
	 */
	public function action($deviceId, $actionName, $actionParam = null)
	{
		//file_put_contents('/usr/share/nginx/www/iss-domo/export.log', $actionName);
		//action for Jeedom
		if (Config::get('hardware.jeedom') == 1){
		
		//*** For Scenes Jeedom***
		if($actionName == 'launchScene'){	
				$arraydeviceId = explode("-", $deviceId);
				$deviceId = $arraydeviceId[0];
		// Curl function
		$url=Config::get('jeedom.jeedom_url')."?apikey=".Config::get('jeedom.api_key')."&type=scenario&id={$deviceId}&action=start";
		$options=array(
			CURLOPT_URL            => $url, 
			CURLOPT_RETURNTRANSFER => true, 
			CURLOPT_HEADER         => false 
		);
		$CURL=curl_init();
		curl_setopt_array($CURL,$options);
		$content=curl_exec($CURL);
		curl_close($CURL);
		// End curl function
		}

      include_once 'jsonrpcClient.class.php';

         $fulldata = $this->getjeedomfull();
         if(isset($fulldata['result'])){
               $test = 'eqLogic_id';
               $cmd = null;
               list($infos, $device_equip) = self::recherche($fulldata, $deviceId, $cmd, $test);
         }
         
         if (isset($infos)) {
            if (($device_equip == 'BeNeXt.BuiltInDimmer' or $device_equip == 'fibaro.fgd211') and $actionName == 'setLevel'){
               $test = 'id';
               $cmd = 'Intensité';
               list($ids, $autre) = self::recherche($fulldata, $infos, $cmd, $test);
               if (isset($ids)) {
                  $jsonrpc = new jsonrpcClient(Config::get('jeedom.jeedom_url'),Config::get('jeedom.api_key'));
                  if ($actionParam == 100) $actionParam = 99;
                  $jsonrpc -> sendRequest('cmd::execCmd',array('id' => $ids, 'options' =>array('slider' => $actionParam)));
               }
            }
            else {
               Switch ($actionName){
                  case 'setStatus' :
                     if ($actionParam == 1){
                        $test = 'id';
                        $cmd = 'On';
                        list($ids, $autre) = self::recherche($fulldata, $infos, $cmd, $test);
                     } elseif ($actionParam == 0){
                        
                        $test = 'id';
                        $cmd = 'Off';
                        list($ids, $autre) = self::recherche($fulldata, $infos, $cmd, $test);
                        
                     }
                     break;
                  case 'setLevel' :               
                     if ($actionParam == 0){
                        $test = 'id';
                        $cmd = 'Down';
                        list($ids, $autre) = self::recherche($fulldata, $infos, $cmd, $test);
                     } elseif ($actionParam == 100){
                        $test = 'id';
                        $cmd = 'Up';
                        list($ids, $autre) = self::recherche($fulldata, $infos, $cmd, $test);
                     }
                     break;
               }
               if (isset($ids)) {
               $jsonrpc = new jsonrpcClient(Config::get('jeedom.jeedom_url'),Config::get('jeedom.api_key'));

               $jsonrpc -> sendRequest('cmd::execCmd',array('id' => $ids));
               }
            }
         }
         
      
         $infos = null;
         $ids = null;
		
        }
		
		// action for Domoticz
		if(Config::get('hardware.domoticz') == 1){
		//*** For switchs, Dimmer, Lock ***
		if($actionName == 'setStatus' OR $actionName == 'pulseShutter'){
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
		
		//*** For Dimmer, Blinds ***
		if($actionName == 'setLevel'){
			if(strpos($deviceId, 'noroom')){
				$arraydeviceId = explode("-", $deviceId);
				$deviceId = $arraydeviceId[0];
			}
		$actionName = 'setLevel' == $actionName ? 'switchlight' : $actionName;
		$actionParam = '0' == $actionParam ? 'On' : 'Off';
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url(Config::get('iss-domo.domoticz_url'), "json.htm?type=command&param={$actionName}&idx={$deviceId}}&switchcmd=$actionParam"));
		$response = $request->send();
		$input = $response->json();

		// convert to app format
		$output = array('success' => ('OK' === $input['status'] ? true : false), 'errormsg' => ('ERR' === $input['status'] ? 'An error occured' : ''));

		return Response::json($output);
		}
		
		//*** For Scenes in Rooms***
		if($actionName == 'launchScene' AND is_numeric($deviceId)){
		$actionName = 'launchScene' == $actionName ? 'switchscene' : $actionName;
		$actionParam = '0' == $actionParam ? 'Off' : 'On';
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url(Config::get('iss-domo.domoticz_url'), "json.htm?type=command&param={$actionName}&idx={$deviceId}}&switchcmd=$actionParam"));
		$response = $request->send();
		$input = $response->json();

		// convert to app format
		$output = array('success' => ('OK' === $input['status'] ? true : false), 'errormsg' => ('ERR' === $input['status'] ? 'An error occured' : ''));

		return Response::json($output);
		}	
		
		//*** For Scenes in No Rooms***
		if($actionName == 'launchScene' AND strpos($deviceId, 'noroomscene')){
				$arraydeviceId = explode("-", $deviceId);
				$deviceId = $arraydeviceId[0];
		$actionName = 'launchScene' == $actionName ? 'switchscene' : $actionName;
		$actionParam = '0' == $actionParam ? 'Off' : 'On';
		$client = $this->getClient();
		$request = $client->getClient()->createRequest('GET', get_url(Config::get('iss-domo.domoticz_url'), "json.htm?type=command&param={$actionName}&idx={$deviceId}}&switchcmd=$actionParam"));
		$response = $request->send();
		$input = $response->json();

		// convert to app format
		$output = array('success' => ('OK' === $input['status'] ? true : false), 'errormsg' => ('ERR' === $input['status'] ? 'An error occured' : ''));

		return Response::json($output);
		}
		}
		
		//*** For MOVIES from XBMC ***
		if(Config::get('hardware.xbmc') == 1 AND Config::get('xbmc.xbmc_movies') == 1){
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
		}
		
		
		//*** For SONGS from XBMC ***
		if(Config::get('hardware.xbmc') == 1 AND Config::get('xbmc.xbmc_songs') == 1){
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
		
      //Device for Jeedom
		if(Config::get('hardware.jeedom') == 1){
		
      $fulldata = $this->getjeedomfull();
      if(isset($fulldata['result'])){
         foreach ($fulldata['result'] as $data) {
         
            foreach ($data['eqLogics'] as $equipment) {
            
               if ($equipment['isVisible'] == 1){
                  if(isset($equipment['configuration']['device'])){
                     $device_equip = $equipment['configuration']['device'];
                  }
                  else {
                     $device_equip = null;
                  }
                  foreach ($equipment['cmds'] as $command) {
                     if ($command['type'] == 'info'){         
                        $params = self::convertJeedomDeviceStatus($command, $device_equip);
                           
                        $output->devices[] = array (
                                       'id' => $command['id'],
                                       'name' => $equipment['name'].'-'.$command['name'],
                                       'type' => self::convertJeedomDeviceType($command, $device_equip),
                                       'room' => $data['id'],
                                       'params' => (null !== $params) ? $params : array()
                                       );
                     }
                     
                  }                  
               }
            }
         }
      }
		
		//Add Scenes Jeedom
		$scenes = $this->getjeedomscene();
		if(isset($scenes['result'])){
			foreach ($scenes['result'] as $sce) {
				if ($sce['isActive'] == 1){
				$output->devices[] = array(
					'id' => $sce['id'].'-scene',
					'name' => $sce['name'],
					'type' => 'DevScene',
					'room' => $sce['object_id'],
					'params' => array( array(
						'key' => 'LastRun',
						'value' => $sce['lastLaunch'],
						),
						),
					);
				}
			}
		}
						
	}
		
		// Device for Domoticz
		if(Config::get('hardware.domoticz') == 1){
		$input = $this->getroom();
		if(isset($input['result'])){
		foreach ($input['result'] as $rooms) {

			$devices = $this->getdevice($rooms['idx']);

			if(isset($devices['result'])){

				foreach ($devices['result'] as $device) {

					$params = self::convertDeviceStatus($device);
					switch($device['Type']) {
						case 'Temp':
							$output->devices[] = array(
										'id' => $device['idx'].'-temp',
										'name' => $device['Name'],
										'type' => 'DevTemperature',
										'room' => $rooms['idx'],
										'params' => array( array(
											'key' => 'Value',
											'value' => $device['Temp'],
											'unit' => '°C',
											'graphable' => 'true',
											),
											),
										);
							//Add history to database
							$db = new SQLite3(Config::get('iss-domo.path_to_db'));
							$milliseconds = round(microtime(true) * 1000);
							$db->exec('INSERT INTO domoticz_history (idx, key, data, date) VALUES ("'.$device['idx'].'-temp","value",'.$device['Temp'].','.$milliseconds.')');
						break;
						case 'Temp + Humidity':
								$output->devices[] = array(
										'id' => $device['idx'].'-temp',
										'name' => $device['Name'],
										'type' => 'DevTemperature',
										'room' => $rooms['idx'],
										'params' => array( array(
											'key' => 'Value',
											'value' => $device['Temp'],
											'unit' => '°C',
											'graphable' => 'true',
											),
											),
										);
								//Add history to database
									$db = new SQLite3(Config::get('iss-domo.path_to_db'));
									$milliseconds = round(microtime(true) * 1000);
									$db->exec('INSERT INTO domoticz_history (idx, key, data, date) VALUES ("'.$device['idx'].'-temp","value",'.$device['Temp'].','.$milliseconds.')');
								$output->devices[] = array(
										'id' => $device['idx'].'-hum',
										'name' => $device['Name'],
										'type' => 'DevHygrometry',
										'room' => $rooms['idx'],
										'params' => array( array(
											'key' => 'Value',
											'value' => $device['Humidity'],
											'unit' => '%',
											'graphable' => 'true',
											),
											),
										);
								//Add history to database
									$db = new SQLite3(Config::get('iss-domo.path_to_db'));
									$milliseconds = round(microtime(true) * 1000);
									$db->exec('INSERT INTO domoticz_history (idx, key, data, date) VALUES ("'.$device['idx'].'-hum","value",'.$device['Humidity'].','.$milliseconds.')');
						break;
						case 'Temp + Humidity + Baro':
								$output->devices[] = array(
										'id' => $device['idx'].'-temp',
										'name' => $device['Name'],
										'type' => 'DevTemperature',
										'room' => $rooms['idx'],
										'params' => array( array(
											'key' => 'Value',
											'value' => $device['Temp'],
											'unit' => '°C',
											'graphable' => 'true',
											),
											),
										);
								//Add history to database
									$db = new SQLite3(Config::get('iss-domo.path_to_db'));
									$milliseconds = round(microtime(true) * 1000);
									$db->exec('INSERT INTO domoticz_history (idx, key, data, date) VALUES ("'.$device['idx'].'-temp","value",'.$device['Temp'].','.$milliseconds.')');
								$output->devices[] = array(
										'id' => $device['idx'].'-hum',
										'name' => $device['Name'],
										'type' => 'DevHygrometry',
										'room' => $rooms['idx'],
										'params' => array( array(
											'key' => 'Value',
											'value' => $device['Humidity'],
											'unit' => '%',
											'graphable' => 'true',
											),
											),
										);
								//Add history to database
									$db = new SQLite3(Config::get('iss-domo.path_to_db'));
									$milliseconds = round(microtime(true) * 1000);
									$db->exec('INSERT INTO domoticz_history (idx, key, data, date) VALUES ("'.$device['idx'].'-hum","value",'.$device['Humidity'].','.$milliseconds.')');
								$output->devices[] = array(
										'id' => $device['idx'].'-press',
										'name' => $device['Name'],
										'type' => 'DevPressure',
										'room' => $rooms['idx'],
										'params' => array( array(
											'key' => 'Value',
											'value' => $device['Barometer'],
											'unit' => 'mbar',
											'graphable' => 'true',
											),
											),
										);
								//Add history to database
									$db = new SQLite3(Config::get('iss-domo.path_to_db'));
									$milliseconds = round(microtime(true) * 1000);
									$db->exec('INSERT INTO domoticz_history (idx, key, data, date) VALUES ("'.$device['idx'].'-press","value",'.$device['Barometer'].','.$milliseconds.')');
						
						break;
						default:
								$output->devices[] = array (
											'id' => $device['idx'],
											'name' => $device['Name'],
											'type' => self::convertDeviceType($device),
											'room' => $rooms['idx'],
											'params' => (null !== $params) ? $params : array(),
											);
						break;
						}
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
					'id' => $sce['idx'].'-noroomscene',
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
				switch($dnr['Type']) {
					case 'Temp':
						$output->devices[] = array(
									'id' => $dnr['idx'].'-temp-noroom',
									'name' => $dnr['Name'],
									'type' => 'DevTemperature',
									'room' => '',
									'params' => array( array(
										'key' => 'Value',
										'value' => $dnr['Temp'],
										'unit' => '°C',
										'graphable' => 'true',
										),
										),
									);
					//Add history to database
							$db = new SQLite3(Config::get('iss-domo.path_to_db'));
							$milliseconds = round(microtime(true) * 1000);
							$db->exec('INSERT INTO domoticz_history (idx, key, data, date) VALUES ("'.$dnr['idx'].'-temp-noroom","value",'.$dnr['Temp'].','.$milliseconds.')');
					break;
					case 'Temp + Humidity':
							$output->devices[] = array(
									'id' => $dnr['idx'].'-temp-noroom',
									'name' => $dnr['Name'],
									'type' => 'DevTemperature',
									'room' => '',
									'params' => array( array(
										'key' => 'Value',
										'value' => $dnr['Temp'],
										'unit' => '°C',
										'graphable' => 'true',
										),
										),
									);
								//Add history to database
									$db = new SQLite3(Config::get('iss-domo.path_to_db'));
									$milliseconds = round(microtime(true) * 1000);
									$db->exec('INSERT INTO domoticz_history (idx, key, data, date) VALUES ("'.$dnr['idx'].'-temp-noroom","value",'.$dnr['Temp'].','.$milliseconds.')');
							$output->devices[] = array(
									'id' => $dnr['idx'].'-hum-noroom',
									'name' => $dnr['Name'],
									'type' => 'DevHygrometry',
									'room' => '',
									'params' => array( array(
										'key' => 'Value',
										'value' => $dnr['Humidity'],
										'unit' => '%',
										'graphable' => 'true',
										),
										),
									);
					//Add history to database
									$db = new SQLite3(Config::get('iss-domo.path_to_db'));
									$milliseconds = round(microtime(true) * 1000);
									$db->exec('INSERT INTO domoticz_history (idx, key, data, date) VALUES ("'.$dnr['idx'].'-hum-noroom","value",'.$dnr['Humidity'].','.$milliseconds.')');
					break;
					case 'Temp + Humidity + Baro':
							$output->devices[] = array(
									'id' => $dnr['idx'].'-temp-noroom',
									'name' => $dnr['Name'],
									'type' => 'DevTemperature',
									'room' => '',
									'params' => array( array(
										'key' => 'Value',
										'value' => $dnr['Temp'],
										'unit' => '°C',
										'graphable' => 'true',
										),
										),
									);
									//Add history to database
										$db = new SQLite3(Config::get('iss-domo.path_to_db'));
										$milliseconds = round(microtime(true) * 1000);
										$db->exec('INSERT INTO domoticz_history (idx, key, data, date) VALUES ("'.$dnr['idx'].'-temp-noroom","value",'.$dnr['Temp'].','.$milliseconds.')');
							$output->devices[] = array(
									'id' => $dnr['idx'].'-hum-noroom',
									'name' => $dnr['Name'],
									'type' => 'DevHygrometry',
									'room' => '',
									'params' => array( array(
										'key' => 'Value',
										'value' => $dnr['Humidity'],
										'unit' => '%',
										'graphable' => 'true',
										),
										),
									);
									//Add history to database
										$db = new SQLite3(Config::get('iss-domo.path_to_db'));
										$milliseconds = round(microtime(true) * 1000);
										$db->exec('INSERT INTO domoticz_history (idx, key, data, date) VALUES ("'.$dnr['idx'].'-hum-noroom","value",'.$dnr['Humidity'].','.$milliseconds.')');
							$output->devices[] = array(
									'id' => $dnr['idx'].'-press-noroom',
									'name' => $dnr['Name'],
									'type' => 'DevPressure',
									'room' => '',
									'params' => array( array(
										'key' => 'Value',
										'value' => $dnr['Barometer'],
										'unit' => 'mbar',
										'graphable' => 'true',
										),
										),
									);
									//Add history to database
										$db = new SQLite3(Config::get('iss-domo.path_to_db'));
										$milliseconds = round(microtime(true) * 1000);
										$db->exec('INSERT INTO domoticz_history (idx, key, data, date) VALUES ("'.$dnr['idx'].'-press-noroom","value",'.$dnr['Barometer'].','.$milliseconds.')');
					break;
					default:
						$output->devices[] = array(
									'id' => $dnr['idx'].'-noroom',
									'name' => $dnr['Name'],
									'type' => self::convertDeviceType($dnr),
									'room' => '',
									'params' => (null !== $params) ? $params : array(),
									);
					break;
					}

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

		return Response::json($output);

	}

   /**
   *DevicetypeforJeedom
   */
   private static function convertJeedomDeviceType($datadevice, $device_equip)
   {
    if(($device_equip == 'BeNeXt.BuiltInDimmer' or $device_equip == 'fibaro.fgd211') and $datadevice['name']=='Etat'){
         $newType='DevDimmer';
   }
   else{
           switch($datadevice['type']){
             case'info':
               switch($datadevice['subType']){
                  case'numeric':
                    switch($datadevice['unite']){
                      case'°C':
                        $newType='DevTemperature';
                        break;
                      case'%':
                        $newType='DevHygrometry';
                        break;
                      case'Pa':
                        $newType='DevPressure';
                        break;
                      case'km/h':
                        $newType='DevWind';
                        break;
                      case'mm/h':
                        $newType='DevRain';
                        break;
                      case'mm':
                        $newType='DevRain';
                        break;
                      case'Lux':
                        $newType='DevLuminosity';
                        break;
                      case'W':
                        $newType='DevElectricity';
                        break;
                     case'KwH':
                        $newType='DevElectricity';
                        break;
                      default:
                        if(isset($datadevice['eqType'])){
                           switch($datadevice['eqType']){
                             case'Store':
                               $newType='DevShutter';
                               break;
                             default:
                               $newType='DevGenericSensor';
                               break;
                           }
                        }
                        else {
                           $newType='DevGenericSensor';
                           break;
                        }
                        break;
                    }
                    
                    break;
                  case'binary':
                    if(isset($datadevice['template']['dashboard'])){
                      switch($datadevice['template']['dashboard']){
                        case'door':
                        case'window':
                        case'porte_garage':
                           $newType='DevDoor';
                           break;
                        case'fire':
                           $newType='DevSmoke';
                           break;
                        case'presence':
                           $newType='DevMotion';
                           break;
                        case'store':
                           $newType='DevShutter';
                           break;
                        default:
                           $newType='DevSwitch';
                           break;
                      }
                    }
                    else{
                      $newType='DevSwitch';
                      break;
                    }
                    break;
                  default:
                    $newType='DevGenericSensor';
                    break;
               }
               break;
             default:
               $newType='DevGenericSensor';
               break;
           }
           
   }
      return $newType;

   }

   /**
   *ParamforJeedom
   */
   private static function convertJeedomDeviceStatus($datadevice, $device_equip){
   if(($device_equip == 'BeNeXt.BuiltInDimmer' or $device_equip == 'fibaro.fgd211') and $datadevice['name']=='Etat'){
      $newType=array(array(
         'key'=>'Level',
         'value'=>$datadevice['state'],
         'unit'=>'%',
       ));
   }
   
   else{
      switch($datadevice['type']){
         case'info':
         switch($datadevice['subType']){
            case'numeric':
               switch($datadevice['unite']){
                  case'°C':
                     $newType=array(array(
                        'key'=>'Value',
                        'value'=>$datadevice['state'],
                        'unit'=>'°C',
						'graphable' => '0' == $datadevice['isHistorized'] ? 'false' : 'true',
                     ));
                     break;
                  case'%':
                     $newType=array(array(
                        'key'=>'Value',
                        'value'=>$datadevice['state'],
                        'unit'=>'%',
						'graphable' => '0' == $datadevice['isHistorized'] ? 'false' : 'true',
                     ));
                     break;
                  case'Pa':
                     $newType=array(array(
                        'key'=>'Value',
                        'value'=>$datadevice['state'],
                        'unit'=>'mbar',
						'graphable' => '0' == $datadevice['isHistorized'] ? 'false' : 'true',
                     ));
                     break;
                  case'km/h':
                     $newType=array(array(
                        'key'=>'Speed',
                        'value'=>$datadevice['state'],
                        'unit'=>'km/h',
                     ));
                     break;
                  case'W':
                     $newType=array(array(
                           'key'=>'Watts',
                           'value'=>$datadevice['state'],
                           'unit'=>'Watt',
                           ),
                        array(
                           'key'=>'ConsoTotal',
                           'value'=>$datadevice['state'],
                           'unit'=>'kWh',
                     ));
                     break;
              case'KwH':
                     $newType=array(array(
                           'key'=>'Watts',
                           'value'=>$datadevice['state'],
                           'unit'=>'KwH',
						   'graphable' => '0' == $datadevice['isHistorized'] ? 'false' : 'true',
                     ));
                     break;
                  case'mm/h':
                     $newType=array(array(
                        'key'=>'Value',
                        'value'=>$datadevice['state'],
                        'unit'=>'mm/h',
                     ));
                     break;
                  case'mm':
                     $newType=array(array(
                        'key'=>'Accumulation',
                        'value'=>$datadevice['state'],
                        'unit'=>'mm',
						'graphable' => '0' == $datadevice['isHistorized'] ? 'false' : 'true',
                     ));
                     break;
                  case'Lux':
                     $newType=array(array(
                        'key'=>'Value',
                        'value'=>$datadevice['state'],
                        'unit'=>'lux',
						'graphable' => '0' == $datadevice['isHistorized'] ? 'false' : 'true',
                     ));
                     break;
                  default:
                     $newType=array(array(
                        'key'=>'Value',
                        'value'=>$datadevice['state'],
                        'unit'=>'',
                     ));
                     break;
               }
               if(isset($datadevice['template']['dashboard'])){
                  switch($datadevice['template']['dashboard']){
                     case'store':
                        $newType=array(array(
                              'key'=>'Level',
                              'value'=>$datadevice['state'],
                           ),
                           array(
                              'key'=>'stopable',
                              'value'=>'1',
                           ),
                           array(
                              'key'=>'pulseable',
                              'value'=>'0',
                        ));
                        break;
                  }
               }
               break;
            case'binary':
               if(isset($datadevice['template']['dashboard'])){
                  switch($datadevice['template']['dashboard']){
                     case'door':
                     case'window':
                     case'porte_garage':
                     case'alert':
                     case'fire':
                     case'presence':
                     case'vibration':
                        $newType=array(array(
                           'key'=>'Tripped',
                           'value'=>$datadevice['state'],
                        ));
                        break;
                     case'lock':
                        $newType=array(array(
                              'key'=>'Tripped',
                              'value'=>$datadevice['state'],
                           ),
                           array(
                              'key'=>'Armed',
                              'value'=>'1',
                           ),
                           array(
                              'key'=>'Ackable',
                              'value'=>'1',
                           ),
                           array(
                              'key'=>'Armable',
                              'value'=>'1',
                        ));
                        break;
                     case'store':
                        $newType=array(array(
                              'key'=>'Level',
                              'value'=>$datadevice['state'],
                           ),
                           array(
                              'key'=>'stopable',
                              'value'=>'1',
                           ),
                           array(
                              'key'=>'pulseable',
                              'value'=>'0',
                        ));
                        break;
                     case'light':
                     case'prise':
                        $newType=array(array(
                           'key'=>'Status',
                           'value'=>$datadevice['state'],
                        ));
                        break;
                     default:
                        $newType=array(array(
                           'key'=>'Status',
                           'value'=>$datadevice['state'],
                        ));
                        break;
                  }
               }
            else{
               $newType=array(array(
                  'key'=>'Status',
                  'value'=>$datadevice['state'],
               ));
            }
            break;
            default:
               $newType=array(array(
                  'key'=>'Value',
                  'value'=>$datadevice['state'],
                  'unit'=>'',
               ));
            break;
         }
         break;
         default:
            $newType=null;
            break;
      }
   }
      return $newType;

   }
	
	/**
	 * Device type for Domoticz
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
					case 'Dusk Sensor':
						$newType = 'DevLuminosity';
						break;
					default:
						$newType = 'DevSwitch';
					break;
				}
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
			case 'RFY':
				$newType = 'DevShutter';
				break;
			case 'Energy':
				$newType = 'DevElectricity';
				break;
			case 'P1 Smart Meter':
				$newType = 'DevElectricity';
				break;
			case 'Usage':
				$newType = 'DevElectricity';
				break;
			case 'Lux':
				$newType = 'DevLuminosity';
				break;
			case 'Current/Energy':
				$newType = 'DevElectricity';
				break;
			case 'Security';
				switch($device['SwitchType']) {
					case 'Smoke Detector':
						$newType = 'DevSmoke';
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
	 * Device Status for Domoticz
	 */	
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
							'value' => 'Closed' == $device['Status'] ? '0' : '1',
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
							'value' => 'Closed' == $device['Status'] ? '0' : '100',
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
							'value' => 'Closed' == $device['Status'] ? '0' : '100',
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
							'value' => 'Closed' == $device['Status'] ? '0' : '100',
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
							'value' => 'Off' == $device['Status'] ? '0' : '100',
						),
						array(
							'key' => 'Status',
							'value' => 'Off' == $device['Status'] ? '0' : '1',
						));
					break;
					case 'Dusk Sensor':
						$output = array( array(
							'key' => 'Value',
							'value' => $device['Data'],
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
			case 'RFY':
						$output = array( array(
							'key' => 'Level',
							'value' => 'Closed' == $device['Status'] ? '0' : '100',
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
			case 'UV':
				$output = array( array(
						'key' => 'Value',
						'value' => $device['UVI'],
						'unit' => 'UVI',
						));
				break;
			case 'Lux':
				$words = preg_split('/\s+/', $device['Data']);
				$data = $words[0];
				$output = array( array(
						'key' => 'Value',
						'value' => $data,
						'unit' => 'lux',
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
				$words = preg_split('/\s+/', $device['Data']);
				$data = $words[0];
				$output = 	array( array(
						'key' => 'Watts',
						'value' => $data,
						'unit' => 'Watt',
						),
						array(
						'key' => 'ConsoTotal',
						'value' => $data,
						'unit' => 'kWh',
						));
				break;
			case 'Current/Energy':
				$words = preg_split('/\s+/', $device['Data']);
				$data = $words[0];
				$output = 	array( array(
						'key' => 'Watts',
						'value' => $data,
						'unit' => 'Watt',
						),
						array(
						'key' => 'ConsoTotal',
						'value' => $data,
						'unit' => 'kWh',
						));
				break;
			case 'Energy':
				$wordsUsage = preg_split('/\s+/', $device['Usage']);
				$dataUsage = $wordsUsage[0];
				$wordsData = preg_split('/\s+/', $device['Data']);
				$dataData = $wordsData[0];
				$output = 	array( array(
						'key' => 'Watts',
						'value' => $dataUsage,
						'unit' => 'Watt',
						),
						array(
						'key' => 'ConsoTotal',
						'value' => $dataData,
						'unit' => 'kWh',
						));
				break;
			case 'P1 Smart Meter':
				$wordsUsage = preg_split('/\s+/', $device['Usage']);
				$dataUsage = $wordsUsage[0];
				$wordsData = preg_split('/\s+/', $device['CounterToday']);
				$dataData = $wordsData[0];
				$output = 	array( array(
						'key' => 'Watts',
						'value' => $dataUsage,
						'unit' => 'Watt',
						),
						array(
						'key' => 'ConsoTotal',
						'value' => $dataData,
						'unit' => 'kWh',
						));
				break;
			case 'Security';
				switch($device['SwitchType']) {
					case 'Smoke Detector':
						$output = array( array(
							'key' => 'Tripped',
							'value' => 'Normal' == $device['Status'] ? '0' : '1',
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
					default:
						$output = null;
						break;
				}
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
