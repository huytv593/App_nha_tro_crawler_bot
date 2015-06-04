<?php
if ( ! defined('VBOT')) exit('No direct script access allowed');

//require config and log
require_once('config.php');
require_once('helper/vlog.php');
 
class connection{
	private $cookie = null;
	
	//init
	public function __construct(){
		$this->cookie = "..".DIRECTORY_SEPARATOR."log".DIRECTORY_SEPARATOR."cookie.txt"; //chi dung duong dan, neu site se load rat cham;
	}
	
	public function get_content($link, $post = '', $cookie_send = false, $session_send = '' ,$follow = false){
		//init log
		$log = Log::log(LOG_ENABLED);
	
		//init curl
		$ch = curl_init();
		if (! $ch) {
			$log->error('Loi trong qua trinh khoi tao cUrl');
		}
	
		$cookies = $this->cookie;
		
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //tra ve string chu k in ra man hinh
		curl_setopt($ch, CURLOPT_URL, $link);
		
		//send session var
		if ($session_send){
			curl_setopt($ch, CURLOPT_COOKIE, $session_send);
		}
		
		//post or get
		if ($post){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		
		//send or recevie cookie
		if (!$cookie_send){
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
		} else {
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);		
		}
		
	
		
		//if follow
		if ($follow){
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		}
		
		$result = curl_exec($ch); //get content
	
		curl_close($ch);
	
		return $result;
	}

	
	//luu file anh
	public function save_photo($remote_image, $save_folder, $image_name){
		//init log
		$log = Log::log(LOG_ENABLED);
		
		//init curl
		$ch = curl_init();
		if (! $ch) {
			$log->error('Loi trong qua trinh khoi tao cUrl');
			return;
		}
		if (!$save_folder){
			$log->error('Khong co folder luu anh');
			return;
		}
		if (!$image_name){
			$log->error('Chua tao ten file moi');
			return;
		}
		
	    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
	    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($ch, CURLOPT_URL, $remote_image);
		
		$result = curl_exec($ch); //get content
		
		curl_close($ch);
		
		//save 
		$new_img = imagecreatefromstring($result);
		imagejpeg($new_img, $save_folder."{$image_name}.jpg",100);
		
		$log->info('Luu file anh '.$remote_image.' -> ' . $image_name . '.jpg thanh cong!');
	}
	
	//aspx sometime need viewstate do postback
	//refer: http://www.mishainthecloud.com/2009/12/screen-scraping-aspnet-application-in.html
	public function get_content_aspx($link){
		//init log
		$log = Log::log(LOG_ENABLED);
		
		//init curl
		$ch = curl_init();
		if (! $ch) {
			$log->error('Loi trong qua trinh khoi tao cUrl');
		}
		
		$cookies = $this->cookie; //chi dung duong dan, neu site se load rat cham
		
		$regexViewstate = '/__VIEWSTATE\" value=\"(.*)\"/i';
		$regexEventVal  = '/__EVENTVALIDATION\" value=\"(.*)\"/i';
		
		//get lan 1 de lay du lieu tho
		curl_setopt($ch, CURLOPT_URL, $link);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$result = curl_exec($ch);
		
		//tach cac bien viewstate
		$viewstate = $this->regexExtract($result,$regexViewstate,$regs,1);
		$eventval = $this->regexExtract($result, $regexEventVal,$regs,1);

		$postData = '__VIEWSTATE='.rawurlencode($viewstate)
		.'&__EVENTVALIDATION='.rawurlencode($eventval)
		;
		
		//lan 2 post data de lay ve cookie
		curl_setOpt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_URL, $link);   
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);   
		  
		$result = curl_exec($ch);
		
		//lan 3 lay noi dung da xu li
		curl_setOpt($ch, CURLOPT_POST, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, 0); 
		//curl_setopt($ch, CURLOPT_HTTPHEADER,array ("Content-Type: text/xml; charset=utf-8"));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //tra ve string chu k in ra man hinh
		curl_setopt($ch, CURLOPT_URL, $link);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
		
		$result = curl_exec($ch);

		curl_close($ch);
		
		return $result;
	}
	
	//aspx sometime need viewstate do postback
	//refer: http://www.mishainthecloud.com/2009/12/screen-scraping-aspnet-application-in.html
	public function get_content_aspx2($link, $post = '', &$viewstate = '' , &$eventval = '',$cookie_send = false, $session_send = ''){
		//init log
		$log = Log::log(LOG_ENABLED);
		
		//init curl
		$ch = curl_init();
		if (! $ch) {
			$log->error('Loi trong qua trinh khoi tao cUrl');
		}

		$cookies = 'cookie.txt';
		
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		//chen bien view state va event validation
		$postData = '';
		if ($viewstate && $eventval){
			$postData = '__VIEWSTATE='.rawurlencode($viewstate)
					.'&__EVENTVALIDATION='.rawurlencode($eventval);	
		}
		if ($post){
			$postData .= '&'.$post;
		}
		if ($viewstate){
			//echo $postData;die;
		}
		if ($post || $viewstate || $eventval){
			curl_setOpt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		}

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_URL, $link);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");

		//send session var
		if ($session_send){
			curl_setopt($ch, CURLOPT_COOKIE, $session_send);
		}
	
		//send or recevie cookie
		if (!$cookie_send){
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
		} else {
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
		}
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);	
			
		$result = curl_exec($ch);		
		curl_close($ch);

		$regexViewstate 	= '/__VIEWSTATE\" value=\"(.*?)\"/i';
		$regexEventVal  	= '/__EVENTVALIDATION\" value=\"(.*?)\"/i';
		
		//tach cac bien viewstate
		$viewstate = $this->regexExtract($result, $regexViewstate,$regs,1);
		$eventval  = $this->regexExtract($result, $regexEventVal,$regs,1);
		
		return $result;
	}
	
	public function get_content_aspx3($link, $post = ''){
		//init log
		$log = Log::log(LOG_ENABLED);
	
		//init curl
		$ch = curl_init();
		if (! $ch) {
			$log->error('Loi trong qua trinh khoi tao cUrl');
		}
	
		$cookies = $this->cookie; //chi dung duong dan, neu site se load rat cham
	
		$regexViewstate = '/__VIEWSTATE\" value=\"(.*?)\"/i';
		$regexEventVal  = '/__EVENTVALIDATION\" value=\"(.*?)\"/i';
	
		//get lan 1 de lay du lieu tho
		curl_setopt($ch, CURLOPT_URL, $link);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$result = curl_exec($ch);
	
		//tach cac bien viewstate
		$viewstate = $this->regexExtract($result,$regexViewstate,$regs,1);
		$eventval = $this->regexExtract($result, $regexEventVal,$regs,1);
	
		$postData = '__VIEWSTATE='.rawurlencode($viewstate)
		.'&__EVENTVALIDATION='.rawurlencode($eventval)
		;
	
		//lan 2 post data de lay ve cookie
		curl_setOpt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_URL, $link);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
	
		$result = curl_exec($ch);
		if ($post){
			$postData .= '&'.$post;
		}
		//lan 3 lay noi dung da xu li
		curl_setOpt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, 0);
		//curl_setopt($ch, CURLOPT_HTTPHEADER,array ("Content-Type: text/xml; charset=utf-8"));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //tra ve string chu k in ra man hinh
		curl_setopt($ch, CURLOPT_URL, $link);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
	
		$result = curl_exec($ch);
	
		curl_close($ch);
	
		return $result;
	}
	
	//tach cac bien 
	public function regexExtract($text, $regex, &$regs, $nthValue)
	{
		if (preg_match($regex, $text, $regs)) {
			$result = $regs[$nthValue];
		}
		else {
			$result = "";
		}
		return $result;
	}
}