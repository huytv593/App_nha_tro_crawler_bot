<?php
if ( ! defined('VBOT')) exit('No direct script access allowed');
/*
 * CLASS TO WRITE LOG
 */
// set the default timezone to use. Available since PHP 5.1
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once('helper/vbuffer.php'); 

class Log{
    private static $log = null;
    private $path 		= 'log';
    private $log_ext	= '.txt'; 
    private $log_name	= null;

    private static $enabled   	= 1; //1 - to write log, 2 - to disable log
    private static $dayclean	= 30; //day to clean log
    private static $print_log	= 1; //print log to screen

    //restrict construct
    private function __construct(){
    }

    // get instance
    public static function log($enabled = LOG_ENABLED, $print_log = LOG_PRINT_SCREEN){
        if (self::$log == null){
                $log = new Log();
                $log->init_log();
                self::$log = $log;
                self::$enabled = $enabled;
                self::$print_log = $print_log;
                self::$dayclean = LOG_CLEAN_DAY;
        } 
        return self::$log;
    }

    //clean log
    public function clean($dayclean = 30){
        if (!$dayclean || !is_numeric($dayclean)) return;
        $path	= $this->path;
        $dir 	= dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.$path;
        $mydir 	= opendir($dir);
        //loop to remove
        while ($file = readdir($mydir)) {
            if($file != "." && $file != "..") {
                if(time() - date("U",filectime($dir.DIRECTORY_SEPARATOR.$file)) >= $dayclean*3600*24)
                unlink($dir.DIRECTORY_SEPARATOR.$file) or die("khong the xoa log $file\n");
            }
        }
        //exist read dir
        closedir($mydir);
    }

    //init log
    private function init_log(){
        if ($this->log_name == null){
            $this->log_name = date('Y-m-d').$this->log_ext;
        }
    }

    //write to log
    private function write($string){
        //log name & path
        $log_name = $this->log_name;
        $path     = $this->path;
        //full path of log
        $log_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$log_name;
        //check log file existent
        $bFileExist = file_exists($log_file);
        if ($bFileExist == true){
            chmod($log_file, 0664);
            $file_handle = fopen($log_file, 'a') or die("can't append file");
        } else {
            $file_handle = fopen($log_file, 'w') or die("can't open file");
        }
        fwrite($file_handle, $string); //write to file
        fclose($file_handle); //close
    }

    //print log to screen
    private function print_log($string){
        vBuffer::buffer_flush();
    }
    
    public function write_log($content, $type){
        $log = $type .': ' .date('Y-m-d H:i:s'). ' : ' .$content ."\n";
        if (self::$enabled) $this->write($log);
        if (self::$print_log) print_r("\n" .$content);
    }

    //write error log
    public function error($log_content){
        $this->write_log($log_content, "ERROR");
    }

    //write info log
    public function info($log_content){
        $this->write_log($log_content, "INFO");
    }

    //write warning log
    public function warn($log_content){
        $this->write_log($log_content, "WARNING");
    }

    //write debug log
    public function debug($log_content){
        $this->write_log($log_content, "DEBUG");
    }

    //write fatal log (most serious error)
    public function fatal($log_content){
        $this->write_log($log_content, "FATAL");
    }
}