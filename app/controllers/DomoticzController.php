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
      returnResponse::json(array(
         'id' => 'ISS-DomoBetaJeedomv1.0.0',
         'apiversion' => 1,
      ));
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

   public function getjeedomdevices()
   {
      include_once 'jsonrpcClient.class.php';

      $jeedomdevices=array();

      $jsonrpc=new jsonrpcClient(Config::get('jeedom.jeedom_url'),Config::get('jeedom.api_key'));

      if($jsonrpc->sendRequest('eqLogic::all',array()))
      {
         $jeedomdevices['result']=$jsonrpc->getResult();
         return$jeedomdevices;
      }
      else
      {
         echo $jsonrpc->getError();
      }
   }

   public function getjeedomcommands()
   {
      include_once'jsonrpcClient.class.php';

      $jeedomcommands = array();

      $jsonrpc=new jsonrpcClient(Config::get('jeedom.jeedom_url'),Config::get('jeedom.api_key'));

      if($jsonrpc -> sendRequest('cmd::all',array()))
      {
         $jeedomcommands['result'] = $jsonrpc->getResult();
         return $jeedomcommands;

      }
      else
      {
         echo $jsonrpc -> getError();
      }
   }

   public function getjeedominfovalues($ids)
   {
      include_once 'jsonrpcClient.class.php';

      $jeedomvalues = array();

      $jsonrpc = new jsonrpcClient(Config::get('jeedom.jeedom_url'),Config::get('jeedom.api_key'));

      if($jsonrpc -> sendRequest('cmd::execCmd',array('id' => $ids)))
      {
         $jeedomvalues['result'] = $jsonrpc -> getResult();
         return $jeedomvalues;
      }
      else
      {
         echo $jsonrpc -> getError();
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
   *Retrievethelistoftherooms.
   *@returnJsonformattedstringlistingallrooms.
   */
   public function searchForId($id,$array)
   {
      foreach ($array as $key => $val){
         if ($val['id'] == $id){
            return $key;
         }
      }
      return null;
   }




   /**
   *Retrievethelistoftheavailabledevices.
   *@returnstringJsonformatteddeviceslist.
   */
   public function devices()
   {
      $output = new stdClass();
      $output -> devices = array();

      //DeviceforJeedom
      if (Config::get('hardware.jeedom') == 1){

         $rooms = $this -> getjeedomrooms();
         $devices = $this -> getjeedomdevices();
         $commands = $this -> getjeedomcommands();

         $infos = array();

         foreach ($commands['result'] as $command){
            if ($command['type'] == 'info' AND $command['isVisible'] == 1){
               $infos[] = $command['id'];
            }
         }

         $commands_value = $this -> getjeedominfovalues($infos);

         foreach ($commands['result'] as $command){

            if ($command['isVisible'] == 1 AND $command['type'] == 'info'){
               $idarraydevices = $this -> searchForId($command['eqLogic_id'],$devices['result']);
               if($devices['result'][$idarraydevices]['object_id']){
                  $idarrayrooms = $this -> searchForId($devices['result'][$idarraydevices]['object_id'],$rooms['result']);
                  $name = $rooms['result'][$idarrayrooms]['name'].'-'.$devices['result'][$idarraydevices]['name'].'-'.$command['name'];
               }
               else{
                  $name = $devices['result'][$idarraydevices]['name'].'-'.$command['name'];
               }
               if(isset($commands_value['result'][$command['id']])){
                  $params = self::convertJeedomDeviceStatus($command,$commands_value['result'][$command['id']]);
               }
               else{
                  $params = self::convertJeedomDeviceStatus($command,$command);
               }
               $output->devices[] = array(
                  'id'=>$command['id'],
                  'name'=>$name,
                  'type'=>self::convertJeedomDeviceType($command),
                  'room'=>$devices['result'][$idarraydevices]['object_id'],
                  'params'=>$params
               );
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
   private static function convertJeedomDeviceStatus($datadevice,$datadeviceB){
      $iddd=$datadevice['id'];

      switch($datadevice['type']){
         case'info':
         switch($datadevice['subType']){
            case'numeric':
               switch($datadevice['unite']){
                  case'°C':
                     $newType=array(array(
                        'key'=>'Value',
                        'value'=>$datadeviceB['value'],
                        'unit'=>'°C',
                     ));
                     break;
                  case'%':
                     $newType=array(array(
                        'key'=>'Value',
                        'value'=>$datadeviceB['value'],
                        'unit'=>'%',
                     ));
                     break;
                  case'Pa':
                     $newType=array(array(
                        'key'=>'Value',
                        'value'=>$datadeviceB['value'],
                        'unit'=>'mbar',
                     ));
                     break;
                  case'km/h':
                     $newType=array(array(
                        'key'=>'Speed',
                        'value'=>$datadeviceB['value'],
                        'unit'=>'km/h',
                     ));
                     break;
                  case'W':
                     $newType=array(array(
                           'key'=>'Watts',
                           'value'=>$datadeviceB['value'],
                           'unit'=>'Watt',
                           ),
                        array(
                           'key'=>'ConsoTotal',
                           'value'=>$datadeviceB['value'],
                           'unit'=>'kWh',
                     ));
                     break;
                  case'mm/h':
                     $newType=array(array(
                        'key'=>'Value',
                        'value'=>$datadeviceB['value'],
                        'unit'=>'mm/h',
                     ));
                     break;
                  case'mm':
                     $newType=array(array(
                        'key'=>'Accumulation',
                        'value'=>$datadeviceB['value'],
                        'unit'=>'mm',
                     ));
                     break;
                  case'Lux':
                     $newType=array(array(
                        'key'=>'Value',
                        'value'=>$datadeviceB['value'],
                        'unit'=>'lux',
                     ));
                     break;
                  default:
                     $newType=array(array(
                        'key'=>'Value',
                        'value'=>$datadeviceB['value'],
                        'unit'=>'',
                     ));
                     break;
               }
               if(isset($datadevice['template']['dashboard'])){
                  switch($datadevice['template']['dashboard']){
                     case'store':
                        $newType=array(array(
                              'key'=>'Level',
                              'value'=>$datadeviceB['value'],
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
                           'value'=>$datadeviceB['value'],
                        ));
                        break;
                     case'lock':
                        $newType=array(array(
                              'key'=>'Tripped',
                              'value'=>$datadeviceB['value'],
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
                              'value'=>$datadeviceB['value'],
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
                           'value'=>$datadeviceB['value'],
                        ));
                        break;
                     default:
                        $newType=array(array(
                           'key'=>'Status',
                           'value'=>$datadeviceB['value'],
                        ));
                        break;
                  }
               }
            else{
               $newType=array(array(
                  'key'=>'Status',
                  'value'=>$datadeviceB['value'],
               ));
            }
            break;
            default:
               $newType=array(array(
                  'key'=>'Value',
                  'value'=>$datadeviceB['value'],
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
