<?php
if ( ! defined('VBOT')) exit('No direct script access allowed');

 class vText{
 	public static function vn_str_filter ($str){
        $unicode = array(
            'a'=>('á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ'),
            'd'=>('đ'),
            'e'=>('é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ'),
            'i'=>('í|ì|ỉ|ĩ|ị'),
            'o'=>('ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ'),
            'u'=>('ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự'),
            'y'=>('ý|ỳ|ỷ|ỹ|ỵ'),
            'A'=>('Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ'),
            'D'=>('Đ'),
            'E'=>('É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ'),
            'I'=>('Í|Ì|Ỉ|Ĩ|Ị'),
            'O'=>('Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ'),
            'U'=>('Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự'),
            'Y'=>('Ý|Ỳ|Ỷ|Ỹ|Ỵ')
        );
       foreach($unicode as $nonUnicode=>$uni){
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
       }
       return $str;
    }

    public static function convertKhongDau($str){
    	return self::vn_str_filter(html_entity_decode($str,ENT_NOQUOTES,"UTF-8"));
    }
    
    //set array param to string
    public static function setParamToString($param){
    	$keys	= 	array_keys($param);
    	$values	=	array_values($param);
    	$param_str	= '';
    	for ($i=0;$i<count($keys);$i++){
    		$param_str	.= '&'.$keys[$i].'='.$values[$i];
    	}
    	$param_str = substr($param_str,1,strlen($param_str)-1);
    	return $param_str;
    }
    
    public static function removeCdataTag($str){
    	$str = str_replace(array('<![CDATA[',']]>','<![CDATA[]]>'),'',$str);
    	return $str;
    }
    
    //cat 1 doan khoang string gioi han
    public static function getSubString($str, $str_start_find, $str_end_find = ''){
    	$strpos = strpos($str, $str_start_find);
    	if ($strpos !== false){
    		$str = substr($str, $strpos + strlen($str_start_find), strlen($str) - $strpos - strlen($str_start_find));
    		//neu co duoi string
    		if ($str_end_find){
    			$strpos2 = strpos($str, $str_end_find);
    			if ($strpos2 !== false){
    				$str = substr($str, 0, $strpos2);
    			}
    		}
    	} else {
    		$str = '';
    	}
    	return $str;
    }
}