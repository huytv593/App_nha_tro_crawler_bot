<?php
if ( ! defined('VBOT')) exit('No direct script access allowed');
require_once('config.php'); 
require_once('db_config.php'); 
require_once('helper/vlog.php'); 

class DBAccess{
    // singleton patern
    private static $db	 = null;
    
    private function __construct(){
    }
    
    //  get instance
    public static function getInstance(){
        if (self::$db == null){
                self::$db = new self();
        }
        return self::$db;
    }
    /**
     *  connect to database 
     */
    private function connect(){
        $log = Log::log(LOG_ENABLED, LOG_PRINT_SCREEN);

        $conn = mysql_connect(BOT_HOST, BOT_USERNAME, BOT_PASSWORD);
        if (!$conn){
                $log->error('Error connecting to db server ' .$conn);
                return null;
        }
        if (!mysql_select_db(BOT_DATABASE)){
                $log->error('Error connecting to database ' .$conn);
                return null;
        }

        mysql_query("set names 'utf8'"); //default set unicode
        return $conn;
    }
    /**
     * close connection
     */
    public function close($conn){
        mysql_close($conn);
    }
    /*
     * get connection
     */
    public function get_connect(){
        return $this->connect();
    }
    
    /*
     * executes an SQL statement  
     */
    public function query($sql){
        $log = Log::log(LOG_ENABLED, LOG_PRINT_SCREEN);
        $conn = $this->connect();
        $result = MYSQL_QUERY ($sql, $conn);
        
        if(!$result){
            $log->info("Error query: $sql" . MYSQL_ERROR());
            die("Error query: $sql" . MYSQL_ERROR());
        }
        if(mysql_num_rows($result) == 0 ) return 0;
        $this->close($conn);
        return $result;
    }

    /**
     * Insert data into table 
     * @param string $table : table name
     * @param array $data : data insert 
     * @param int $type : 1 => insert one row | 2 => insert multiply row, $data is two-dimensional array
     */
    public function insert($table, $data, $type = 1){
        $sql = "INSERT INTO `$table` (";
        $value_str = '';
        if($type == 1){
            foreach ($data as $key => $value){
//                $value = preg_replace("/\'/", "/\\'/", $value);
//                $value = preg_replace('/\"/', "/\\'/", $value);
                $value = mysql_real_escape_string($value);
                $sql .= $key . ',';
                $value_str .= "'" .$value. "'" .',';
            }
            $sql = preg_replace('/\,$/', '', $sql);
            $value_str = preg_replace('/\,$/', '', $value_str);
            $sql .= ") VALUES ( $value_str )";
            $this->query($sql);
            return mysql_insert_id();
        }else if($type == 2){  
            $n = count($data);
            for($i = 0; $i < $n; $i++){
                $showData = $data[$i];
                $tmp_value_str = '(';
                foreach ($showData as $ep_key => $ep_value){
                    $ep_value = preg_replace("/\'/", "/\\'/", $ep_value);
                    $ep_value = preg_replace('/\"/', "/\\'/", $ep_value);
                    //$ep_value = mysql_real_escape_string($ep_value);
                    if($i == 0)$into_field .= $ep_key . ',';
                    $tmp_value_str .= "'" .$ep_value. "'" .',';
                }
                $tmp_value_str = preg_replace('/\,$/', '', $tmp_value_str);
                $value_str .= $tmp_value_str .'),';
            }
            $into_field = preg_replace('/\,$/', '', $into_field);
            $value_str = preg_replace('/\,$/', '', $value_str);
            $sql .= $into_field .") VALUES $value_str";

            $this->query($sql);
           return true;
        }
        return false;
    }

    /**
     * Update table with condition
     * @param type $table : table name
     * @param type $data : data update
     * @param type $condition : where condition
     * @return type
     */
    public function update($table, $data, $condition){
        $sql = "UPDATE $table SET" . ' ';
        foreach ($data as $key => $value){
            $sql .= $key . ' = \'' . $value .'\',';
        }
        $sql = preg_replace('/\,$/', '', $sql);
        $sql .= ' '."WHERE" .' ';
        foreach ($condition as $key => $value){
            $sql .= ' ' .$key . ' = ' ."'". $value ."'". ' and';
        }
        $sql = preg_replace('/and$/', '', $sql) .';';

        return $this->query($sql);
    }

    /**
     * Select from table
     * @param string $table : table name
     * @param array $parameters : from field
     * @param array $where : where condition 
     * @param string $order : order by 
     * @param int $limit : limit 
     * @return type
     */
    public function select($table, $parameters = '*', $where = null, $order = null, $limit = null){
        $sql = 'SELECT';
        if($parameters == '*'){
            $sql .= ' * ';
        }else{
            foreach ($parameters as $key => $value){
                $sql .= ' '. $value .',';
            }
        }

        $sql = preg_replace('/\,$/', '', $sql);
        $sql .= ' FROM ' ."`" .$table ."`";

        if($where != null && !empty($where)){
            $sql .= ' WHERE ';
            foreach ($where as $key => $value){
//                $value = preg_replace("/\'/", "/\\'/", $value);
//                $value = preg_replace('/\"/', "/\\'/", $value);
                $value = mysql_real_escape_string($value);
                $sql .= ' ' .$key . '=' ."'" .$value ."'" .' and';
            }
            $sql = preg_replace('/and$/', '', $sql);
        }
        if($order != null){
            $sql .= ' ORDER BY ' . $order;
        }
        if($limit != null){
            $sql .= ' LIMIT ' .$limit;
        }
        $sql .= ';';
        return $this->query($sql);
    }
    
    public function getLastId($table, $field){
        $sql = "SELECT $field FROM `$table` ORDER BY $field DESC LIMIT 1";
        $query = $this->query($sql);
        $result = $this->result_array($query);
        return $result[$field];
    }

    /**
     * convert query result to object
     * @param type $query_result
     * @return object 
     */
    public function result_object($query_result){
        $result = mysql_fetch_object($query_result);
        return $result;
    }
    
    /**
     *  convert query result to array
     * @param type $query_result
     * @return array
     */
    public function result_array($query_result){
         return mysql_fetch_array($query_result, MYSQL_BOTH);
    }
    /*
     * set names 
     */
    public function set_names($name, $collate){
        mysql_query("SET NAMES $name COLLATE $collate ", $this->connection);
    }

    /*
     * begin transaction
     */
    public function transaction_begin(){
        mysql_query("SET AUTOCOMMIT = 0");
        mysql_query("START TRANSACTION");
    }

    /*
     * commit transaction
     */
    public function transaction_commit(){
        mysql_query("COMMIT");
    }

    /*
     * roll back
     */
    public function transaction_rollback(){
        mysql_query("ROLLBACK");
    }
}