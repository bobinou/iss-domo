<?php

use\Goutte\Client;

class DomoticzController extends BaseController
{
   /**
   *GenericHttpclient.
   *@var\Goutte\Client
   */
   private static $client;

   /**
   *Getsysteminformations.
   *@returnstringJsonformattedsysteminformations.
   */
   public function system()
   {
      return Response::json(array(
         'id' => 'ISS-Domo Beta Jeedom v2.0.0',
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
   *GetJeedomdata.
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

   


/** Public function for actions Jeedom **/
public function action($deviceId, $actionName, $actionParam = null)
   {
      //DeviceforJeedom
      if (Config::get('hardware.jeedom') == 1){
      
      include_once 'jsonrpcClient.class.php';

		$fulldata = $this->getjeedomfull();
		if(isset($fulldata['result'])){
			foreach ($fulldata['result'] as $data) {			
				foreach ($data['eqLogics'] as $equipment) {	
						foreach ($equipment['cmds'] as $command) {
				            if ($command['id'] == $deviceId){
               					$infos = $command['eqLogic_id'];
               				}
               				
               				if ($actionName == 'setStatus' AND $actionParam == 1){
               					if ($command['eqLogic_id'] == $infos and $command['name'] == 'On'){
                  					$ids = $command['id'];
               					}
            				}
         
         					elseif ($actionName == 'setStatus' AND $actionParam == 0){
               					if ($command['eqLogic_id'] == $infos and $command['name'] == 'Off'){
                  					$ids = $command['id'];
               					}
            				}

         $jsonrpc = new jsonrpcClient(Config::get('jeedom.jeedom_url'),Config::get('jeedom.api_key'));

         $jsonrpc -> sendRequest('cmd::execCmd',array('id' => $ids));
      
      				}
      			}
      		}
      	}
      }
   }


   /**
   *Retrievethelistoftherooms.
   *@returnJsonformattedstringlistingallrooms.
   */
   public function rooms()
   {
      //converttoappformat
      $output = new stdClass();
      $output -> rooms = array();

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

      return Response::json($output);

   }



   /**
   *Retrievethelistoftheavailabledevices.
   *@returnstringJsonformatteddeviceslist.
   */
   public function devices()
   {
      $output = new stdClass();
      $output -> devices = array();

      //Device for Jeedom
		if(Config::get('hardware.jeedom') == 1){
		
		$fulldata = $this->getjeedomfull();
		if(isset($fulldata['result'])){
			foreach ($fulldata['result'] as $data) {
			
				foreach ($data['eqLogics'] as $equipment) {
				
					if ($equipment['isVisible'] == 1){
					
						foreach ($equipment['cmds'] as $command) {
											
						$params = self::convertJeedomDeviceStatus($command);
							
						$output->devices[] = array (
											'id' => $command['id'],
											'name' => $equipment['name'].'-'.$command['name'],
											'type' => self::convertJeedomDeviceType($command),
											'room' => $data['id'],
											'params' => (null !== $params) ? $params : array()
											);
											
						}						
					}
				}
			}
		}
						
	}

      return Response::json($output);

   }

   /**
   *DevicetypeforJeedom
   */
   private static function convertJeedomDeviceType($datadevice)
   {
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
                     default:
                        $newType='DevGenericSensor';
                        break;
                  }
                  
                  break;
               case'binary':
                  if(isset($datadevice['template']['dashboard'])){
                     switch($datadevice['template']['dashboard']){
                        case'door':
                        case'window':
                        case'garage':
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
      return $newType;

   }

   /**
   *ParamforJeedom
   */
   private static function convertJeedomDeviceStatus($datadevice){

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
                     ));
                     break;
                  case'%':
                     $newType=array(array(
                        'key'=>'Value',
                        'value'=>$datadevice['state'],
                        'unit'=>'%',
                     ));
                     break;
                  case'Pa':
                     $newType=array(array(
                        'key'=>'Value',
                        'value'=>$datadevice['state'],
                        'unit'=>'mbar',
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
                           'value'=>$$datadevice['state'],
                           'unit'=>'kWh',
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
                     ));
                     break;
                  case'Lux':
                     $newType=array(array(
                        'key'=>'Value',
                        'value'=>$datadevice['state'],
                        'unit'=>'lux',
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
                     case'garage':
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
      return $newType;

   }

   /**
   *Getthehttpclientinstance.
   *@return\Goutte\Client
   */
   private function getClient()
   {
      if(null==self::$client){
      self::$client = newClient();
      }

      returnself::$client;
   }
}
