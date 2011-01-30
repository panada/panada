<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada MySQL Database API.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman. Inspired by ezSQL {@link http://php.justinvincent.com Justin Vincent (justin@visunet.ie)}
 * @since	Version 0.1
 */

class Library_db {
    
    public $link;
    public $insert_id;
    public $last_query;
    public $last_error;
    public $client_flags = 0;
    public $connection;
    public $new_link = true;
    public $persistent_connection = false;
    
    /**
     * EN: Define all properties needed.
     * @return void
     */
    function __construct($connection = 'default'){
        $this->config = new Library_config();
	$this->connection = $connection;
	$this->persistent_connection = $this->config->db->$connection->persistent;
    }
    
    /**
     * EN: Establish a new connection to mysql server
     *
     * @return string | boolean MySQL persistent link identifier on success, or FALSE on failure.
     */
    private function establish_connection(){
	
	$connection = $this->connection;
	$arguments = array(
			$this->config->db->$connection->host,
			$this->config->db->$connection->user,
			$this->config->db->$connection->password,
			$this->new_link,
			$this->client_flags
		    );
	$function = 'mysql_connect';
	
	if( $this->persistent_connection ){
	    $arguments = array(
			$this->config->db->$connection->host,
			$this->config->db->$connection->user,
			$this->config->db->$connection->password,
			$this->client_flags
		    );
	    $function = 'mysql_pconnect';
	}
	
	return call_user_func_array($function, $arguments);
    }
    
    /**
     * EN: Inital for all process
     *
     * @return void
     */
    private function init(){
	
	$connection = $this->connection;
	
	if( is_null($this->link) )
	    $this->link = $this->establish_connection();
        
        if ( ! $this->link ){
	    $this->error = new Library_error();
            $this->error->database('Unable connet to database in <strong>'.$connection.'</strong> connection.');
        }
        
        $collation_query = '';
        
        if ( ! empty($this->config->db->$connection->charset) ) {
            $collation_query = "SET NAMES '".$this->config->db->$connection->charset."'";
	    if ( ! empty($this->config->db->$connection->collate) )
                $collation_query .= " COLLATE '".$this->config->db->$connection->collate."'";
	}
	
        if ( ! empty($collation_query) )
            $this->query($collation_query);
        
        $this->select_db($this->config->db->$connection->database);
    }
    
    /**
     * EN: Select the databse
     *
     * @return void
     */
    private function select_db($dbname){
	
	if( is_null($this->link) )
	    $this->init();
        
        if ( ! @mysql_select_db($dbname, $this->link) )
            Library_error::database('Unable to select database in <strong>'.$this->connection.'</strong> connection.');
        
    }
    
    /**
     * EN: Escape all unescaped string
     *
     * @param string $string
     * @return void
     */
    public function escape($string){
        
	if( is_null($this->link) )
	    $this->init();
	
        return mysql_real_escape_string($string, $this->link);
    }
    
    /**
     * EN: Main function for querying to database
     *
     * @param $query The SQL querey statement
     * @return string|objet Return the resource id of query
     */
    public function query($sql){
	
	if( is_null($this->link) )
	    $this->init();
        
        $query = mysql_query($sql, $this->link);
        $this->last_query = $sql;
        
        if ( $this->last_error = mysql_error($this->link) ) {
            $this->print_error();
            return false;
        }
        
        if( $insert_id = mysql_insert_id($this->link) )
            $this->insert_id = $insert_id;
        
        return $query;
    }
    
    /**
     * EN: Get multiple records
     *
     * @param string $query The sql query
     * @param string $type return data type option. the default is "object"
     */
    public function results($query, $type = 'object'){
        
        $result = $this->query($query);
        
        while ($row = @mysql_fetch_object($result)) {
            
            if($type == 'array')
                $return[] = (array) $row;
            else
                $return[] = $row;
        }
        
        @mysql_free_result($result);
        
        return @$return;
    }
    
    /**
     * EN: Get single record
     *
     * @param string $query The sql query
     * @param string $type return data type option. the default is "object"
     */
    public function row($query, $type = 'object'){
	
	if( is_null($this->link) )
	    $this->init();
        
        $result = $this->query($query);
        $return = mysql_fetch_object($result);
        
        if($type == 'array')
            return (array) $return;
        else
            return $return;
    }
    
    /**
     * EN: Get value directly from single field
     *
     * @param string @query
     * @return string|int Depen on it record value.
     */
    public function get_var($query) {
        
        $result = $this->row($query);
        $key = array_keys(get_object_vars($result));
        
        return $result->$key[0];
    }
    
    /**
     * EN: Abstraction for get records
     *
     * @param string $table Table name
     * @param array $where 'WHERE' sql statement eg: id = '1' ... The default value is NULL or no "WHERE"
     * @param array $fields table fileds name. If this parameter empty so it will SELECT * (select all fields)
     * @return string The SQL statement
     */
    public function get($table, $where = array(), $fields = array()){
        
        //EN: If field table undefined, then select all.
        if ( empty($fields) ) {
            $field = '*';
        }
        else {
            
            foreach($fields as $fields)
                $f[] = '`'.$fields.'`';
            
            $field = implode( ', ', $f );
        }
        
        if ( ! empty( $where ) ) {
            
            $bits = $wheres = array();
            foreach ( (array) array_keys($where) as $k )
                $bits[] = "`$k` = '$where[$k]'";
            
            foreach ( $where as $c => $v )
                $wheres[] = "`$c` = '" . $this->escape( $v ) . "'";
            
            $where = "WHERE ". implode( ' AND ', $wheres );
        }
        else {
            $where = '';
        }
        
        return "SELECT $field FROM `$table` " . $where;
    }
    
    /**
     * EN: Abstraction to get single record
     *
     * @param string
     * @param array Default si null
     * @param array Default is all
     * @return object
     */
    public function get_row($table, $where = array(), $fields = array()){
        
        $query = $this->get($table, $where, $fields);
        return $this->row($query);
        
    }
    
    /**
     * EN: Abstraction to get multyple records
     *
     * @param string
     * @param array Default si null
     * @param array Default is all
     * @return object
     */
    public function get_results($table, $where = array(), $fields = array()){
        
        $query = $this->get($table, $where, $fields);
        return $this->results($query);
    }
    
    /**
     * EN: Abstraction for insert
     *
     * @param string $table
     * @param array $data
     * @return boolean
     */
    public function insert($table, $data = array()) {
        
        $fields = array_keys($data);
        
        foreach($data as $key => $val)
            $escaped_date[$key] = $this->escape($val);
        
        return $this->query("INSERT INTO `$table` (`" . implode('`,`',$fields) . "`) VALUES ('".implode("','",$escaped_date)."')");
    }
    
    /**
     * EN: Abstraction for replace
     *
     * @param string $table
     * @param array $data
     * @return boolean
     */
    public function replace($table, $data = array()) {
        
        $fields = array_keys($data);
        
        foreach($data as $key => $val)
            $escaped_date[$key] = $this->escape($val);
        
        return $this->query("REPLACE INTO `$table` (`" . implode('`,`',$fields) . "`) VALUES ('".implode("','",$escaped_date)."')");
    }
    
    /**
     * EN: Abstraction for update
     *
     * @param string $table
     * @param array $dat
     * @param array $where
     * @return boolean
     */
    public function update($table, $dat, $where){
        
        foreach($dat as $key => $val)
            $data[$key] = $this->escape($val);
        
        $bits = $wheres = array();
        foreach ( (array) array_keys($data) as $k )
            $bits[] = "`$k` = '$data[$k]'";
        
        if ( is_array( $where ) )
            foreach ( $where as $c => $v )
                $wheres[] = "$c = '" . $this->escape( $v ) . "'";
        else
            return false;
        
        return $this->query( "UPDATE `$table` SET " . implode( ', ', $bits ) . ' WHERE ' . implode( ' AND ', $wheres ) );
    }
    
    /**
     * EN: Abstraction for delete
     *
     * @param string
     * @param array
     * @return boolean
     */
    public function delete($table, $where){
        
        if ( is_array( $where ) )
            foreach ( $where as $c => $v )
                $wheres[] = "$c = '" . $this->escape( $v ) . "'";
        else
            return false;
        
        return $this->query( "DELETE FROM `$table` WHERE " . implode( ' AND ', $wheres ) );
    }
    
    /**
     * EN: Print the error at least to PHP error log file
     *
     * @return string
     */
    private function print_error() {
    
        if ( $caller = Library_error::get_caller(2) )
            $error_str = sprintf('Database error %1$s for query %2$s made by %3$s', $this->last_error, $this->last_query, $caller);
        else
            $error_str = sprintf('Database error %1$s for query %2$s', $this->last_error, $this->last_query);
    
        //write the error to log
        @error_log($error_str, 0);
    
        //Is error output turned on or not..
        if ( error_reporting() == 0 )
            return false;
    
        $str = htmlspecialchars($this->last_error, ENT_QUOTES);
        $query = htmlspecialchars($this->last_query, ENT_QUOTES);
    
        // If there is an error then take note of it
        Library_error::database($str.'<br /><b>Query</b>: '.$query.'<br /><b>Backtrace</b>: '.$caller);
    }
    
}// End library_mysql