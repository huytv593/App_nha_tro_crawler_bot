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
//require_once('helper/DBAccess.php');

class Batdongsan implements iplugin
{
    public $id = 'BATDONGSAN';
    public $name = 'BATDONGSAN';
    public $connect = null;
    public $log;
    public $done = 0;
    public $err = 0;
    public $image_path = './img/';
    public $link = array();
    public $xpath = array();
    public function __contruct() {

    }

    public function setXpath()
    {
        $xpath = array(
            'max_panigate' =>"//div[@class='background-pager-right-controls']/a[7]",
            'item' => "//div[@class='Main']/div",
            'title' => "//div[@id='product-detail']/div[@class='pm-title']/h1/node()[not(self::h1)]",
            'address' => "//div[@class='left-detail']/div[1]/div[@class='right']/node()[not(self::div)]",
            'price' => "//div[@class='kqchitiet']/span[2]/span[1]/strong/node()[not(self::strong)]",
            'detail' => "//div[@class='pm-content stat']/node()[not(self::div|self::br)]",
            'img' => "//div[@id='photoSlide']/div[@id='divPhotoActive']/div/img",
            'area' => "//div[@class='kqchitiet']/span[2]/span[2]/strong/node()[not(self::strong)]",
            'created_at' => "//div[@class='left-detail']/div[4]/div[@class='right']/node()[not(self::div)]",
            'end_at' => "//div[@class='left-detail']/div[5]/div[@class='right']/node()[not(self::div)]",
            'contact_name' => "//div[@id='LeftMainContent__productDetail_contactName']/div[2]/node()[not(self::div)]",
            'contact_phone' => "//div[@id='LeftMainContent__productDetail_contactPhone']/div[2]/node()[not(self::div)]",
            // 'contact_email' => "//div[@id='LeftMainContent__productDetail_contactEmail']/div[2]/a/node()[not(self::a)]",
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

    public function connectDB(){
        $servername = "localhost";
        $username = "root";
        $password = "mysql";
        $dbname = "php_bot";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }  else {
            echo "Connect DB sucsess: <br>";
        }
        $conn->set_charset("utf8");
        return $conn;
        }

    public function getCoordinates($address = null){
        $address = urlencode($address);
        $url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=" . $address;
        $response = file_get_contents($url);
        $json = json_decode($response,true);
        $lat = $json['results'][0]['geometry']['location']['lat'];
        $lng = $json['results'][0]['geometry']['location']['lng'];
        // var_dump($url);
        // var_dump($response);
        return array($lat, $lng);
    }

    /**
     * @return null
     */
    public function start()
    {
        $this->logStartTime();
        $conn = $this->connectDB();
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


        $paginate = 100;
        for ($ic = 1; $ic <= $paginate; $ic++) {
            if($ic== 1)  $link = $this->link['domain'];
            else   $link = 'http://batdongsan.com.vn/cho-thue-nha-tro-phong-tro/p'.$ic;
            $result = $this->getItem($link);
            foreach ($result as $key => $value) {
                $data[] = $this->getData($value, $conn);
            }
        }
        $this->logEndTime();
    }

    function getItem($link = null) {
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
        return $result;
    }

    public function getData($nhatro_link = null, $conn = null) {
        $content = $this->connect->get_content($nhatro_link);
        if ($content == '') {
            $this->log->warn('Not found item content ' . $nhatro_link . '1');
            return null;
        };

        $objhtml = new Html();
        $objhtml->init($content);

        $nhatro = array();

        $tmp_title =  trim($objhtml->get_xpath_content($this->xpath['title']));
        $nhatro['title'] = html_entity_decode(strip_tags($tmp_title),ENT_NOQUOTES,"UTF-8");

        $tmp_address =  html_entity_decode(strip_tags(trim($objhtml->get_xpath_content($this->xpath['address']))),ENT_NOQUOTES,"UTF-8");
        $coords = $this->getCoordinates($tmp_address);
        $nhatro['latit'] = $coords['0'];
        $nhatro['longit'] = $coords['1'];
        $tmp_pos = strrpos($tmp_address, ', ');
        $nhatro['city'] = substr($tmp_address, $tmp_pos+2);
        $tmp_tpe = substr($tmp_address, 0 , $tmp_pos);
        $tmp_pos = strrpos($tmp_tpe, ', ');
        $nhatro['district'] = substr($tmp_tpe, $tmp_pos +2);
        $nhatro['address'] = substr($tmp_tpe, 0 , $tmp_pos);

        $tmp_price =  trim($objhtml->get_xpath_content($this->xpath['price']));
        $tmp_price = (float)strstr($tmp_price, ' ', true);
        $nhatro['price'] =  (float)$tmp_price * 1000000;

        $nhatro['detail'] =  html_entity_decode(strip_tags(trim($objhtml->get_xpath_content($this->xpath['detail']))),ENT_NOQUOTES,"UTF-8");
        $tmp_area =  trim($objhtml->get_xpath_content($this->xpath['area']));
        if(strstr($tmp_area, 'm', true))
            $nhatro['area'] =  strstr($tmp_area, 'm', true);
        else $nhatro['area'] = 0;

        $nhatro['created_at'] =  trim($objhtml->get_xpath_content($this->xpath['created_at']));
        $tmp_create = date_create_from_format("d-m-Y", $nhatro['created_at']);
        $nhatro['created_at'] = date_format($tmp_create,'Y-m-d');

        $nhatro['end_at'] =  trim($objhtml->get_xpath_content($this->xpath['end_at']));
        $tmp_create = date_create_from_format("d-m-Y", $nhatro['end_at']);
        $nhatro['end_at'] = date_format($tmp_create,'Y-m-d');

        $nhatro['contact_name'] =  html_entity_decode(strip_tags(trim($objhtml->get_xpath_content($this->xpath['contact_name']))),ENT_NOQUOTES,"UTF-8");

        $nhatro['contact_phone'] =  trim($objhtml->get_xpath_content($this->xpath['contact_phone']));

        $img_xpath =  $objhtml->get_xpath_content($this->xpath['img']);
        if($img_xpath != '') {
            $img_a_xpath = $objhtml->get_xpath_attr($this->xpath['img'].'[1]');
            $img_a_link = $img_a_xpath['src'];
            $nhatro['imga'] = $img_a_link;
        } else $nhatro['imga'] = '';

        //Save data to mysql;
        $result = array();
        $result['title'] = $nhatro['title'];
        $query = "INSERT INTO nhatros (title, created_by, created_at, end_at, price, city, district, precinct, street, address, area, info, imga, imgb, imgc, imgd, longit, latit, contact_name, contact_phone) VALUE ("
            ."'".$nhatro['title']."',"
            ."0,"
            ."'".$nhatro['created_at']."',"
            ."'".$nhatro['end_at']."',"
            .$nhatro['price'].","
            ."'".$nhatro['city']."',"
            ."'".$nhatro['district']."',"
            ."'',"
            ."'',"
            ."'".$nhatro['address']."',"
            .$nhatro['area'].","
            ."'".$nhatro['detail']."',"
            ."'".$nhatro['imga']."',"
            ."'',"
            ."'',"
            ."'',"
            ."'".$nhatro['longit']."',"
            ."'".$nhatro['latit']."',"
            ."'".$nhatro['contact_name']."',"
            ."'".$nhatro['contact_phone']."'
            )";
        if($conn->query($query) === TRUE){ $result['result'] = '=>>> done'; $this->done++;}
        else { $result['result'] = $conn->error; $this->err++;}
        echo $result['title'].' latit: '.$nhatro['latit'].$result['result'].'<br>';
        // var_dump($nhatro); die;
        return $nhatro;
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
        echo 'Done: '.$this->done;
        echo 'Err: '.$this->err;
    }
}
?>