<?php
if ( ! defined('VBOT')) exit('No direct script access allowed');

require_once('connection/connection.php');
require_once('interface/iplugin.php');
require_once('helper/vlog.php');
require_once('helper/vhtml.php');
require_once('helper/vtext.php');

class get24htt implements iplugin{
    public $id = 'get24htt';
    public $name = 'get24h11';
    public $connect = null;
    public $log;
    public $image_path = 'D:/img/';
    
    public $link = array();
    public $xpath = array();

    public function __contruct(){
        
    }
    
    public function setXpath(){
        $xpath = array(
            'item'      => ".//div[@class='photoList']/div[@class='photoListItem']",
//            'img'       => "//div[@class='item clearfix'][%ITEM_ROW%]/div[@class='contentwrapper clearfix']/div/div[1]/a/img",
//            'link_cnt'  => "//div[@class='item clearfix'][%ITEM_ROW%]/div[@class='contentwrapper clearfix']/div/div[@class='likebox new']/div[1]/a/span",
            );
        $this->xpath = $xpath;
        return $this->xpath;
    }
   
    public function setLink(){
        $link = array(
            'domain' => 'http://www.haivl.com/',
        );
        $this->link = $link;
        return $this->link;
    }

    public function start(){
        $this->logStartTime();
        
        //init
        $this->setLink();
        $this->setXpath();
        
        // construct connection
        $this->connect = new connection();
        
        // get first content
        $link = $this->link['domain'];
        $content = $this->connect->get_content($link);
        if ($content == ''){
            $this->log->warn('Not found page content ' .$this->domain .'1');
            return null;
        }
        
        $objhtml = new Html();
        $objhtml->init($content);
        
        $item_sum_xpath = $this->xpath['item'];
        $item_sum = $objhtml->get_xpath_node_length($item_sum_xpath);
        var_dump($item_sum);
        die('TT');
        for($ic = 1; $ic < $item_sum; $ic++){
            $img_xpath = $this->xpath['item'] . "[$ic]/div[@class='contentwrapper clearfix']/div/div[1]/a/img";
            $img_item = $objhtml->get_xpath_attr($img_xpath);
            $title = $img_item['alt'];
            $link_img = $img_item['src'];
            
            //lua anh
            $pos = strrpos($item_img['src'],'/');
            $tmp_img = substr($item_img['src'], $pos+1);
            $tmp_poster = file_get_contents($item_img['src']);
            file_put_contents($img_path.$tmp_img, $tmp_poster);
            
            $like_xpath = $this->xpath['item'] . "[$ic]/div[@class='contentwrapper clearfix']/div/div[@class='likebox new']/div[1]/a/span";
            $likeCnt = html_entity_decode(strip_tags($objhtml->get_xpath_content($like_xpath)),ENT_NOQUOTES,"UTF-8");
            
            var_dump($likeCnt);
            var_dump($img_item);
            var_dump($title);
            var_dump($link_img);
            die;
        }
        
        $this->logEndTime();
    }
    function getData(){
        
    }
    
    function logStartTime(){
        $now = date('Y/m/d H:i:s');
        $this->log = Log::log(LOG_ENABLED, LOG_PRINT_SCREEN);
        $this->log->info('[START CRAWLER] AT: ' .$now);
    }
    
    function logEndTime(){
        $now = date('Y/m/d H:i:s');
        $this->log = Log::log(LOG_ENABLED, LOG_PRINT_SCREEN);
        $this->log->info('[END CRAWLER AT]: ' .$now);
    }
   
}
?>