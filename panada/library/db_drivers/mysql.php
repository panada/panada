<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada MySQL Database Driver.
 *
 * @package	Panada
 * @subpackage	Driver
 * @author	Iskandar Soesman.
 * @since	Version 0.1
 */

class Driver_mysql {
    
    protected $column = '*';
    protected $distinct_ = false;
    protected $tables = null;
    protected $criteria = array();
    protected $group_by_ = null;
    protected $limit_ = null;
    protected $offset_ = null;
    protected $order_by_ = null;
    protected $order_ = null;
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
     * EN: Establish a new connection to mysql server
     *
     * @return string | boolean MySQL persistent link identifier on success, or FALSE on failure.
     */
    private function establish_connection(){
	
	$arguments = array(
			$this->db_config->host,
			$this->db_config->user,
			$this->db_config->password,
			$this->new_link,
			$this->client_flags
		    );
	
	$function = 'mysql_connect';
	
	if( $this->db_config->persistent ){
	    
	    $arguments = array(
			$this->db_config->host,
			$this->db_config->user,
			$this->db_config->password,
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
	
	if( is_null($this->link) )
	    $this->link = $this->establish_connection();
        
        if ( ! $this->link ){
	    $this->error = new Library_error();
            $this->error->database('Unable connet to database in <strong>'.$this->connection.'</strong> connection.');
        }
        
        $collation_query = '';
        
        if ( ! empty($this->db_config->charset) ) {
            $collation_query = "SET NAMES '".$this->db_config->charset."'";
	    if ( ! empty($this->db_config->collate) )
                $collation_query .= " COLLATE '".$this->db_config->collate."'";
	}
	
        if ( ! empty($collation_query) )
            $this->query($collation_query);
        
        $this->select_db($this->db_config->database);
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
    
    public function select(){
        
	$column = func_get_args();
	
        if( ! empty($column) )
	    $this->column = $column;
        
        return $this;
    }
    
    public function distinct(){
	
	$this->distinct_ = true;
	return $this;
    }
    
    public function from(){
	
	$this->tables = implode(', ', func_get_args());
	return $this;
    }
    
    public function where($column, $operator, $value, $next_operator = false){
        
	if( is_array($value) )
	    $value = "('".implode("', '", $value)."')";
	
	$this->criteria[] = $column.' '.$operator.' '.$value;
	
	if($next_operator)
	    $this->criteria[] .= ' '.$next_operator;
	
        return $this;
    }
    
    public function group_by(){
	
	$this->group_by_ = implode(', ', func_get_args());
	return $this;
    }
    
    public function order_by($column, $order = null){
	
	$this->order_by_ = $column;
	$this->order_ = $order;
	
	return $this;
    }
    
    public function limit($limit, $offset = null){
	
	$this->limit_ = $limit;
	$this->offset_ = $offset;
	
	return $this;
    }
    
    public function _command(){
        
        $query = 'SELECT ';
	
	if($this->distinct_)
	    $query .= 'DISTINCT ';
        
        $column = '*';
        
        if( is_array($this->column) )
            $column = implode(', ', $this->column);
        
        $query .= $column;
        
        if( ! is_null($this->tables) )
            $query .= ' FROM '.$this->tables;
	
	if( ! empty($this->criteria) )
	    $query .= ' WHERE '.implode(' ', $this->criteria);
	
	if( ! is_null($this->group_by_) )
	    $query .= ' GROUP BY '.$this->group_by_;
	
	if( ! is_null($this->order_by_) )
	    $query .= ' ORDER BY '.$this->order_by_.' '.$this->order_;
	
	
	if( ! is_null($this->limit_) ){
	    
	    $query .= ' LIMIT';
	    
	    if( ! is_null($this->offset_) )
		$query .= ' '.$this->offset_.' ,';
	    
	    $query .= ' '.$this->limit_;
	}
        die($query);
        return $query;
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
    public function results($query = null, $type = 'object'){
        
	if( is_null($query) )
	    $query = $this->_command();
	
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
    public function row($query = null, $type = 'object'){
	
	if( is_null($query) )
	    $query = $this->_command();
	
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
     * EN: Abstraction to get single record
     *
     * @param string
     * @param array Default si null
     * @param array Default is all
     * @return object
     */
    public function get_row( $table, $where = array(), $fields = array() ){
        
	if( ! empty($fields) )
	    call_user_func_array(array($this, 'select'), $fields);
	
	$this->from($table);
	
	if ( ! empty( $where ) ) {
	    
	    $seperator = 'AND';
            foreach($where as $key => $val){
		
		if( end($where) == $val)
		    $seperator = false;
		
		$this->where($key, '=', $val, $seperator);
            }
        }
	
        return $this->row();
        
    }
    
    /**
     * EN: Abstraction to get multple records
     *
     * @param string
     * @param array Default si null
     * @param array Default is all
     * @return object
     */
    public function get_results($table, $where = array(), $fields = array()){
	
	if( ! empty($fields) )
	    call_user_func_array(array($this, 'select'), $fields);
	
	$this->from($table);
	
	if ( ! empty( $where ) ) {
	    
	    $seperator = 'AND';
            foreach($where as $key => $val){
		
		if( end($where) == $val)
		    $seperator = false;
		
		$this->where($key, '=', $val, $seperator);
            }
        }
	
        return $this->results();
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
     * Get the id form last insert
     *
     * @return int
     */
    public function insert_id(){
	
	return @mysql_insert_id($this->link);
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
    
    /**
     * Get this db version
     *
     * @return void
     */
    public function version(){
	
	return $this->get_var("SELECT version() AS version");
    }
    
} // End Driver_mysql Class