<?php
/**
* BeckHoff ADS 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 15:07:46 [Jul 04, 2020])
*/
//
//
class dev_beckhoff_ads extends module {
/**
* dev_beckhoff_ads
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="dev_beckhoff_ads";
  $this->title="BeckHoff ADS";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=1) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 $this->getConfig();
 $out['API_URL']=$this->config['API_URL'];
 if (!$out['API_URL']) {
  $out['API_URL']='http://';
 }
 $out['API_KEY']=$this->config['API_KEY'];
 $out['API_USERNAME']=$this->config['API_USERNAME'];
 $out['API_PASSWORD']=$this->config['API_PASSWORD'];
 if ($this->view_mode=='update_settings') {
   global $api_url;
   $this->config['API_URL']=$api_url;
   global $api_key;
   $this->config['API_KEY']=$api_key;
   global $api_username;
   $this->config['API_USERNAME']=$api_username;
   global $api_password;
   $this->config['API_PASSWORD']=$api_password;
   $this->saveConfig();
   $this->redirect("?");
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='beckhoff_variables' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_beckhoff_variables') {
   $this->search_beckhoff_variables($out);
  }
  if ($this->view_mode=='edit_beckhoff_variables') {
   $this->edit_beckhoff_variables($out, $this->id);
  }
  if ($this->view_mode=='delete_beckhoff_variables') {
   $this->delete_beckhoff_variables($this->id);
   $this->redirect("?data_source=beckhoff_variables");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='beckhoff_variables_data') {
  if ($this->view_mode=='' || $this->view_mode=='search_beckhoff_variables_data') {
   $this->search_beckhoff_variables_data($out);
  }
  if ($this->view_mode=='edit_beckhoff_variables_data') {
   $this->edit_beckhoff_variables_data($out, $this->id);
  }
 }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* beckhoff_variables search
*
* @access public
*/
 function search_beckhoff_variables(&$out) {
  require(DIR_MODULES.$this->name.'/beckhoff_variables_search.inc.php');
 }
/**
* beckhoff_variables edit/add
*
* @access public
*/
 function edit_beckhoff_variables(&$out, $id) {
  require(DIR_MODULES.$this->name.'/beckhoff_variables_edit.inc.php');
 }
/**
* beckhoff_variables delete record
*
* @access public
*/
 function delete_beckhoff_variables($id) {
  $rec=SQLSelectOne("SELECT * FROM beckhoff_variables WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM beckhoff_variables WHERE ID='".$rec['ID']."'");
 }
/**
* beckhoff_variables_data search
*
* @access public
*/
 function search_beckhoff_variables_data(&$out) {
  require(DIR_MODULES.$this->name.'/beckhoff_variables_data_search.inc.php');
 }
/**
* beckhoff_variables_data edit/add
*
* @access public
*/
 function edit_beckhoff_variables_data(&$out, $id) {
  require(DIR_MODULES.$this->name.'/beckhoff_variables_data_edit.inc.php');
 }
 function propertySetHandle($object, $property, $value) {
  $this->getConfig();
   $table='beckhoff_variables_data';
   $properties=SQLSelect("SELECT ID FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
     //to-do
    }
   }
 }
 function processCycle() {
 $this->getConfig();
  //to-do
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS beckhoff_variables');
  SQLExec('DROP TABLE IF EXISTS beckhoff_variables_data');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
beckhoff_variables - 
beckhoff_variables_data - 
*/
  $data = <<<EOD
 beckhoff_variables: ID int(10) unsigned NOT NULL auto_increment
 beckhoff_variables: TITLE varchar(100) NOT NULL DEFAULT ''
 beckhoff_variables: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 beckhoff_variables: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 beckhoff_variables: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
 beckhoff_variables: UPDATED datetime
 beckhoff_variables_data: ID int(10) unsigned NOT NULL auto_increment
 beckhoff_variables_data: TITLE varchar(100) NOT NULL DEFAULT ''
 beckhoff_variables_data: VALUE varchar(255) NOT NULL DEFAULT ''
 beckhoff_variables_data: variable_id int(10) NOT NULL DEFAULT '0'
 beckhoff_variables_data: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 beckhoff_variables_data: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 beckhoff_variables_data: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
 beckhoff_variables_data: UPDATED datetime
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSnVsIDA0LCAyMDIwIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
