<?php
if ( ! defined('VBOT')) exit('No direct script access allowed');

class vBuffer{
	//show echo immediately
	public static function buffer_flush(){
		//don't under stand white need echo 2 string before must show :)
		echo str_pad('', 512);
		echo '<!-- -->';
		if(ob_get_length()){
			@ob_flush();
			@flush();
			@ob_end_flush();
		}
		@ob_start();
	}
}