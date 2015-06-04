<?php
if ( ! defined('VBOT')) exit('No direct script access allowed');

require_once('connection/connection.php');
require_once('interface/iplugin.php');
require_once('helper/vlog.php');
require_once('helper/vhtml.php');
require_once('helper/vtext.php');

class Haivl implements iplugin{
    public $id = 'HAIVL';
    public $name = 'HAIVL';
    public $connect = null;
    public $log;
    public $image_path = './img/';
    
    public $link = array();
    public $xpath = array();

    public function __contruct(){
        
    }
    
    public function setXpath(){
        $xpath = array(
            'item'      => "//div[@class='content_news']",
//            'img'       => "//div[@class='item clearfix'][%ITEM_ROW%]/div[@class='contentwrapper clearfix']/div/div[1]/a/img",
//            'link_cnt'  => "//div[@class='item clearfix'][%ITEM_ROW%]/div[@class='contentwrapper clearfix']/div/div[@class='likebox new']/div[1]/a/span",
            );
        $this->xpath = $xpath;
        return $this->xpath;
    }
   
    public function setLink(){
        $link = array(
            'domain' => 'http://kenh14.vn/la-cool.chn',
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
            $this->log->warn('Not found page content ');
            return null;
        }
        
        $objhtml = new Html();
        $objhtml->init($content);
        
        $item_sum_xpath = $this->xpath['item'];
        $item_sum = $objhtml->get_xpath_node_length($item_sum_xpath);
        var_dump($item_sum);
        //die('TT');
        
        for($ic = 1; $ic < $item_sum; $ic++){
            $img_xpath = $this->xpath['item'] . "[$ic]/div[@class='avatar-type-1']/a/img";
            // var_dump($img_xpath);
            $img_item = $objhtml->get_xpath_attr($img_xpath);
            $title_img = $img_item['alt'];
            $link_img = $img_item['src'];
            // var_dump($img_item);
            
            $title_xpath = $this->xpath['item'] . "[$ic]/h2/a";
            $title_item = $objhtml->get_xpath_attr($title_xpath);
            $title_link = $title_item['href'];
            
            //lua anh
            $pos = strrpos($img_item['src'],'/');
            $tmp_img = substr($img_item['src'], $pos+1);
            $tmp_poster = file_get_contents($img_item['src']);
            file_put_contents($this->image_path.$tmp_img, $tmp_poster);
            
            // var_dump($tmp_img);
            // var_dump($img_item['src']);
            // die;
            
            // $view_xpath = $this->xpath['item'] . "[$ic]/div[@class='info']/div[@class='stats']/div[@class='viewComments']/span[@class='views']";
            // $view_cnt = html_entity_decode(strip_tags($objhtml->get_xpath_content($view_xpath)),ENT_NOQUOTES,"UTF-8");
            // $view_cnt = trim($view_cnt);
            $data = array(
                'title' => $title_link,
                'link_img' => $link_img,
                'like_cnt' => $likeCnt,
                // 'view_cnt' => $view_cnt,
                
            );
            $result[] = $data;
//            var_dump($likeCnt);
//            var_dump($img_item);
//            var_dump($title);
//            var_dump($link_img);
//            die;
        }
        
        //in ra ket qua
        print_r($result);
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