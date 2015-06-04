<?php
if ( ! defined('VBOT')) exit('No direct script access allowed');
require_once('config.php');
require_once('helper/vlog.php');

class vMail{
	private static $vmail = null;
	private static $enabled   	= 1; //1 - to write, 2 - to disable
	
	//restrict construct
	private function __construct(){
	}
	
	//function init
	public static function vmail($enabled = 1){
		if (self::$vmail == null){
			$vmail = new vMail();
			self::$vmail   = $vmail;
			self::$enabled = $enabled;
		}
		return self::$vmail;
	}
	
	//send mail
	public function send($subject, $message){
		if (self::$enabled){
			$to     = SUPPORT_MAIL_TO;
			$from	= SUPPORT_MAIL_FROM;
	        $header = "From: ".$from."\nContent-Type: text/plain; charset=\"utf-8\"\nContent-Transfer-Encoding: 8bit";
	
	        $result = mail($to, vText::convertKhongDau($subject), $message, $headers);
	        $log 	 = Log::log(LOG_ENABLED, LOG_PRINT_SCREEN);
	        if ($result){
	       		$log->info('Gui mail toi '.SUPPORT_MAIL_TO.' thanh cong !');
	        } else {
	        	$log->error('Co loi gui mail!');
	    	}	
		}
	}
}