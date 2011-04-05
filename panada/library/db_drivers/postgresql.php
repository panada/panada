<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada PostgreSQL Database Driver.
 *
 * @package	Panada
 * @subpackage	Driver
 * @author	Iskandar Soesman.
 * @since	Version 0.3
 */

class Driver_postgresql {
    
    private $link;
    private $connection;
    private $db_config;
    private $last_query;
    private $last_error;
    public $insert_id;
    public $client_flags = 0;
    public $new_link = true;
    public $persistent_connection = false;
    
    /**
     * EN: Define all properties needed.
     * @return void
     */
    function __construct( $config_instance, $connection_name ){
	
	$this->db_config = $config_instance;
	$this->connection = $connection_name;
	
    }
    
    /**
     * EN: Establish a new connection to postgreSQL server
     *
     * @return string | boolean postgreSQL persistent link identifier on success, or FALSE on failure.
     */
    private function establish_connection(){
        
        $arguments = 'host='.$this->db_config->host.'
                    port=5432
                    dbname='.$this->db_config->database.'
                    user='.$this->db_config->user.'
                    password='.$this->db_config->password.'
                    options=\'--client_encoding='.$this->db_config->charset.'\'';
        
	$arguments = array(
			$arguments,
			false
		    );
	$function = 'pg_connect';
	
	if( $this->db_config->persistent )
	    $function = 'pg_pconnect';
	
	return call_user_func_array($function, $arguments);
    }
    
    /**
     * EN: Inital for all process
     *
     * @return void
     */
    private function init(){
	
	if( is_null($this->link) )
	    $this->link = $this->establish_connection();
        
        if ( ! $this->link ){
	    $this->error = new Library_error();
            $this->error->database('Unable connet to database in <strong>'.$this->connection.'</strong> connection.');
        }
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
	
        return pg_escape_string($this->link, $string);
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
        
        $query = pg_query($this->link, $sql);
        $this->last_query = $sql;
        
        if ( $this->last_error = pg_last_error($this->link) ) {
            $this->print_error();
            return false;
        }
        
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
        
        while ($row = @pg_fetch_object($result)) {
            
            if($type == 'array')
                $return[] = (array) $row;
            else
                $return[] = $row;
        }
        
        @pg_free_result($result);
        
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
        $return = pg_fetch_object($result);
        
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
                $f[] = $fields;
            
            $field = implode( ', ', $f );
        }
        
        if ( ! empty( $where ) ) {
            
            $bits = $wheres = array();
            foreach ( (array) array_keys($where) as $k )
                $bits[] = "$k = '$where[$k]'";
            
            foreach ( $where as $c => $v )
                $wheres[] = "$c = '" . $this->escape( $v ) . "'";
            
            $where = "WHERE ". implode( ' AND ', $wheres );
        }
        else {
            $where = '';
        }
        
        return "SELECT $field FROM $table " . $where;
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
        
        $insert = $this->query("INSERT INTO $table (" . implode(',',$fields) . ") VALUES ('".implode("','",$escaped_date)."')");
        
        $this->insert_id = $this->get_var("SELECT LASTVAL() as ins_id");
        
        return $insert;
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
        
        return $this->query( "UPDATE $table SET " . implode( ', ', $bits ) . ' WHERE ' . implode( ' AND ', $wheres ) );
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
        
        return $this->query( "DELETE FROM $table WHERE " . implode( ' AND ', $wheres ) );
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
    
    /**
     * Get this db version
     *
     * @return void
     */
    public function version(){
	
	return $this->get_var("SELECT version() AS version");
    }
    
    /**
     * Get the id form last insert
     *
     * @return int
     */
    public function insert_id(){
	
	return $this->get_var("SELECT LASTVAL() as ins_id");
    }
    
}// End library_mysql