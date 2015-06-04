<?php
if ( ! defined('VBOT')) exit('No direct script access allowed');
require_once('config.php'); 
require_once('helper/vlog.php'); 

class vBot{
	private $connection = null;
	public $arr_plug	= array();
	
	//initialize
	public function __construct(){
		error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
		ini_set('max_execution_time', BOT_MAX_EXECUTION_TIME); //tang thoi gian execution len
		$this->init_connect(); //init connection variable
	}
	
	//function get content and save to db
	public function run($arr_link){
		$log 	 = Log::log(LOG_ENABLED, LOG_PRINT_SCREEN);
		$connect = $this->connection;
		$plug	 = $this->arr_plug;
		//no plugin return
		if(count($plug) == 0) {
			$log->info('2. Khong co plugin nao de chay crawl'.'<br>');
			return; 
		}
		//run crawl
		foreach($arr_link as $id){
			if(isset($plug[$id])){
				$plugin = $plug[$id];
				//START BOT
				$log->info('2. Bat dau chay plugin '. $plugin->name.'<br>');
				//RUNNING
				$plugin->connect = $connect;
				$plugin->start();
				//END BOT
				$log->info('<br>'.'Ket thuc chay plugin '. $plugin->name.'<br>');
				if(BOT_DELAY) sleep(BOT_DELAY);
			}else{
				//warning load
				$log->warn('Khong co plugin '. $id).'<br>';
			}
		}	
	}
	
	//run plugin batch
	public function load_plugin($arr_plugin){
		$log = Log::log(LOG_ENABLED, LOG_PRINT_SCREEN);
		$path = dirname(__FILE__).DIRECTORY_SEPARATOR.'plugin'.DIRECTORY_SEPARATOR;
		for ($i=0; $i<count($arr_plugin); $i++){
			//check existent
			if (file_exists($path.strtolower($arr_plugin[$i]).'.php')){
                            
				//load plugin
				require_once('plugin'.DIRECTORY_SEPARATOR.strtolower($arr_plugin[$i]).'.php');	
				$plugin_name = strtolower($arr_plugin[$i]); //class name
				$plugin		 = new $plugin_name; //create plugin
				//set to array
				$arr[$plugin->id] = $plugin;
			}else{
                            echo file_exists($path.strtolower($arr_plugin[$i]).'.php');
				//warning load
				$log->warn('Khong co plugin '. $arr_plugin[$i] .'!');
			}
		}
		$this->arr_plug =  $arr;
	}
	
	//remove log old day
	public function clean($clean_day){	
		$log = Log::log(LOG_ENABLED, LOG_PRINT_SCREEN);
		$log->info('1. Xoa cac file log cu trong gioi han: ' . $clean_day .' ngay!'.'<br>');
		$log->clean($clean_day);
		$log->info('Da xoa file log cu thanh cong'.'<br>');
	}
	
	//init connection - file get content or curl
	public function init_connect(){
		if ($this->connection == null) {
			if (CONNECTION_PROTOCOL == 1){
				require_once 'connection/fget_contents.php';
			}else{
				require_once 'connection/connection.php';		
			}
			$this->connection = new connection();
		} 
	}
}