<?php
chdir(dirname(__FILE__) . '/../');
include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");
set_time_limit(0);
// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);
include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
$ctl = new control_modules();
include_once(DIR_MODULES . 'dev_beckhoff_ads/dev_beckhoff_ads.class.php');
$dev_beckhoff_ads_module = new dev_beckhoff_ads();
$dev_beckhoff_ads_module->getConfig();
$tmp = SQLSelectOne("SELECT ID FROM beckhoff_variables LIMIT 1");
#if (!$tmp['ID'])
#   exit; // no devices added -- no need to run this cycle
echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;
$latest_check=0;
$checkEvery=5; // poll every 5 seconds

#echo DIR_MODULES.'dev_beckhoff_ads/run_variables';
$variables = array( 
    "bHallLightMain"=> array(
			'type'=>'BOOL',
			'def'=>false
		),
    "bHallLightBack"=> array(
			'type'=>'BOOL',
			'def'=> false,
		),
    "iHallLightDimmer"=>array(
			'type'=>'BYTE',
			'def'=> 0
		),
    "bKitchenLightMain"=> array(
			'type' => 'BOOL',
			'def'=> false,
		),
    "bKitchenLightBack"=> array(
			'type' => 'BOOL',
			'def'=> false,
		),
    "bKitchenLightSub"=> array(
			'type'=>'BOOL',
			'def'=>false,
		),
    "bKitchenLightWork"=> array(
			'type'=>'BOOL',
			'def'=> false,
		),
    "bMainDoorSMK"=>array(
			'type'=>'BOOL',
			'def'=>true,
		),
    "bMainDoorLock"=> array(
			'type'=>'BOOL',
			'def'=>false,
		),
    "bSubDoorSMK"=> array(
			'type'=>'BOOL',
			'def'=> false,
		),
    "bSubDoorLock"=> array(
			'type'=>'BOOL',
			'def'=> false
		),
    "bTerm0Power"=> array(
			'type'=>'BOOL',
			'def'=> false
		),
    "wTerm0TempSensor"=> array(
			'type'=>'WORD',
			'def'=> 0
		),
    "wTerm0TempSetting"=> array(
			'type'=>'WORD',
			'def'=> 0
		),
    "bPumpState"=> array(
			'type'=>'BOOL',
			'def'=> false
		),

);
$var_file=DIR_MODULES.'dev_beckhoff_ads/run_variables';
$srv_file=DIR_MODULES.'dev_beckhoff_ads/dev_beckhoff_ads.py';
file_put_contents ($var_file,json_encode($variables));
//Запускаем ракету
#exec (''.$srv_file.' '.$var_file.' >> /tmp/dev_beckhoff_ads.log &',$out,$retvar);
#var_dump ($out);
#var_dump ($retvar);
while (1)
{
   setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
   if ((time()-$latest_check)>$checkEvery) {
    $latest_check=time();
    echo date('Y-m-d H:i:s').' Polling devices...';
    $dev_beckhoff_ads_module->processCycle();
   }
   if (file_exists('./reboot') || IsSet($_GET['onetime']))
   { //Видимо тут процедура выходa
      $db->Disconnect();
      exit;
   }
   sleep(1);
}
DebMes("Unexpected close of cycle: " . basename(__FILE__));
