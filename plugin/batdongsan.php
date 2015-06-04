<?php
/**
 * Created by PhpStorm.
 * User: huytv
 * Date: 6/4/15
 * Time: 22:52
 */

if (!defined('VBOT')) exit('No direct script access allowed');

require_once('connection/connection.php');
require_once('interface/iplugin.php');
require_once('helper/vlog.php');
require_once('helper/vhtml.php');
require_once('helper/vtext.php');

class Batdongsan implements iplugin
{
    public $id = 'BATDONGSAN';
    public $name = 'BATDONGSAN';
    public $connect = null;
    public $log;
    public $image_path = './img/';
    public $link = array();
    public $xpath = array();
    public function __contruct() {

    }

    public function setXpath()
    {
        $xpath = array(
            'item' => "//div[@class='Main']/div",
            // 'img'       => "//div[@class='content_news']/div[@class='avatar-type-1']/a/img",
            // 'link_cnt'  => "//div[@class='content_news']/h2/a",
        );
        $this->xpath = $xpath;
        return $this->xpath;
    }

    public function setLink()
    {
        $link = array(
            'domain' => 'http://batdongsan.com.vn/cho-thue-nha-tro-phong-tro',
        );
        $this->link = $link;
        return $this->link;
    }

    public function start()
    {
        $this->logStartTime();

        //init
        $this->setLink();
        $this->setXpath();

        // construct connection
        $this->connect = new connection();

        // get first content
        $link = $this->link['domain'];
        $content = $this->connect->get_content($link);
        if ($content == '') {
            $this->log->warn('Not found page content ' . $this->domain . '1');
            return null;
        }

        $objhtml = new Html();
        $objhtml->init($content);
        $item_sum_xpath = $this->xpath['item'];

        $item_sum = $objhtml->get_xpath_node_length($item_sum_xpath);
        for ($ic = 3; $ic <= $item_sum; $ic++) {
            $nhatro_xpath = $this->xpath['item'] . "[$ic]/div[@class='p-title']/a";
            $nhatro_item = $objhtml->get_xpath_attr($nhatro_xpath);
            $nhatro_link = 'http://batdongsan.com.vn'.$nhatro_item['href'];

            $result[] = $nhatro_link;
        }

        var_dump($result);
        $this->logEndTime();
    }

    function getData()
    {

    }

    function getItem($nhatro_link = null) {
        $content = $this->connect->get_content($nhatro_link);
        if ($content == '') {
            $this->log->warn('Not found item content ' . $nhatro_link . '1');
            return null;
        }

        $objhtml = new Html();
        $objhtml->init($content);


    }

    function logStartTime()
    {
        $now = date('Y/m/d H:i:s');
        $this->log = Log::log(LOG_ENABLED, LOG_PRINT_SCREEN);
        $this->log->info('[START CRAWLER] AT: ' . $now . '<br>');
    }

    function logEndTime()
    {
        $now = date('Y/m/d H:i:s');
        $this->log = Log::log(LOG_ENABLED, LOG_PRINT_SCREEN);
        $this->log->info('[END CRAWLER AT]: ' . $now . '<br>');
    }

}

?>