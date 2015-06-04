<?php
if ( ! defined('VBOT')) exit('No direct script access allowed');

require_once('connection/connection.php');
require_once('interface/iplugin.php');
require_once('helper/vlog.php');
require_once('helper/vhtml.php');
require_once('helper/vtext.php');

class Kenh14 implements iplugin{
    public $id = 'KENH14';
    public $name = 'KENH14';
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
            // 'img'       => "//div[@class='content_news']/div[@class='avatar-type-1']/a/img",
             // 'link_cnt'  => "//div[@class='content_news']/h2/a",
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
            $this->log->warn('Not found page content ' .$this->domain .'1');
            return null;
        }
        
        $objhtml = new Html();
        $objhtml->init($content);
        
        $item_sum_xpath = $this->xpath['item'];
        $item_sum = $objhtml->get_xpath_node_length($item_sum_xpath);
        var_dump($item_sum);
        for($ic = 1; $ic < $item_sum; $ic++){
            $img_xpath = $this->xpath['item'] . "[$ic]/div[@class='avatar-type-1']/a/img";
            $img_item = $objhtml->get_xpath_attr($img_xpath);
            $title = $img_item['alt'];
            $link_img = $img_item['src'];
            
            //lua anh   
            $img_path = "./img/";
            $pos = strrpos($img_item['src'],'/');
            $tmp_img = substr($img_item['src'], $pos+1);
            $tmp_poster = file_get_contents($img_item['src']);
            if(file_put_contents($img_path.$tmp_img, $tmp_poster)) echo 'luu anh thanh cong';
            
            $like_xpath = $this->xpath['item'] . "[$ic]/h2/a";
            $likeCnt = html_entity_decode(strip_tags($objhtml->get_xpath_content($like_xpath)),ENT_NOQUOTES,"UTF-8");
            
            var_dump($likeCnt);echo '<br>';
            var_dump($title);echo '<br>';
            var_dump($link_img);echo '<br>';
            echo '--------<br>';
        }
        
        $this->logEndTime();
    }
    function getData(){
        
    }
    
    function logStartTime(){
        $now = date('Y/m/d H:i:s');
        $this->log = Log::log(LOG_ENABLED, LOG_PRINT_SCREEN);
        $this->log->info('[START CRAWLER] AT: ' .$now.'<br>');
    }
    
    function logEndTime(){
        $now = date('Y/m/d H:i:s');
        $this->log = Log::log(LOG_ENABLED, LOG_PRINT_SCREEN);
        $this->log->info('[END CRAWLER AT]: ' .$now.'<br>');
    }
   
}
?>