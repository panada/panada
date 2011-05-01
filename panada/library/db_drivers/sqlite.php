<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada SQLite Database Driver.
 *
 * @package	Panada
 * @subpackage	Driver
 * @author	Iskandar Soesman.
 * @since	Version 0.3
 */

class Driver_sqlite {
    
    protected $column = '*';
    protected $distinct_ = false;
    protected $tables = null;
    protected $joins = null;
    protected $joins_type = null;
    protected $joins_on = array();
    protected $criteria = array();
    protected $group_by_ = null;
    protected $is_having = array();
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
     * Establish a new connection to SQLite server
     *
     */
    private function establish_connection(){
	
	if( ! $this->link = new SQLite3( $this->db_config->database, SQLITE3_OPEN_READWRITE ) ){
	    
	    $this->error = new Library_error();
            $this->error->database('Unable connet to database in <strong>'.$this->connection.'</strong> connection.');
	}
    }
    
    /**
     * Inital for all process
     *
     * @return void
     */
    private function init(){
	
	if( is_null($this->link) )
	    $this->establish_connection();
    }
    
    /**
     * API for "SELECT ... " statement.
     *
     * @param string $column1, $column2 etc ...
     * @return object
     */
    public function select(){
        
	$column = func_get_args();
	
        if( ! empty($column) ){
	    $this->column = $column;
	    
	    if( is_array($column[0]) )
		$this->column = $column[0];
        }
        
        return $this;
    }
    
    /**
     * API for "... DISTINCT " statement.
     *
     * @return object
     */
    public function distinct(){
	
	$this->distinct_ = true;
	return $this;
    }
    
    /**
     * API for "...FROM ... " statement.
     *
     * @param string $table1, $table2 etc ...
     * @return object
     */
    public function from(){
	
	$tables = func_get_args();
	
	if( is_array($tables[0]) )
	    $tables = $tables[0];
	
	$this->tables = implode(', ', $tables);
	
	return $this;
    }
    
    /**
     * API for "... JOIN ..." statement.
     *
     * @param string $table Table to join
     * @param string $type Type of join: LEFT, RIGHT, INNER
     */
    public function join($table, $type = null){
	
	$this->joins = $table;
	$this->joins_type = $type;
	
	return $this;
    }
    
    /**
     * Create criteria condition. It use in on, where and having method
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param mix $separator
     */
    protected function create_criteria($column, $operator, $value, $separator){
	
	if( is_string($value) ){
	    $value = $this->escape($value);
	    $value = " '$value'";
	}
	
	if( $operator == 'IN' )
	    if( is_array($value) )
		$value = "('".implode("', '", $value)."')";
	
	if( $operator == 'BETWEEN' )
	    $value = $value[0].' AND '.$value[1];
	
	$return = $column.' '.$operator.' '.$value;
	
	if($separator)
	    $return .= ' '.strtoupper($separator);
	
	return $return;
    }
    
    /**
     * API for "... JOIN ON..." statement.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param mix $separator
     */
    public function on($column, $operator, $value, $separator = false){
	
	$this->joins_on[] = $this->create_criteria($column, $operator, $value, $separator);
	
        return $this;
    }
    
    /**
     * API for "... WHERE ... " statement.
     *
     * @param string $column Column name
     * @param string $operator SQL operator string: =,<,>,<= dll
     * @param string $value Where value
     * @param string $separator Such as: AND, OR
     * @return object
     */
    public function where($column, $operator, $value, $separator = false){
        
	$this->criteria[] = $this->create_criteria($column, $operator, $value, $separator);
	
        return $this;
    }
    
    /**
     * API for "... GROUP BY ... " statement.
     *
     * @param string $column1, $column2 etc ...
     * @return object
     */
    public function group_by(){
	
	$this->group_by_ = implode(', ', func_get_args());
	return $this;
    }
    
    /**
     * API for "... HAVING..." statement.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param mix $separator
     */
    public function having($column, $operator, $value, $separator = false){
	
	$this->is_having[] = $this->create_criteria($column, $operator, $value, $separator);
	
        return $this;
    }
    
    /**
     * API for "... ORDER BY..." statement.
     *
     * @param string $column1, $column2 etc ...
     * @return object
     */
    public function order_by($column, $order = null){
	
	$this->order_by_ = $column;
	$this->order_ = $order;
	
	return $this;
    }
    
    /**
     * API for "... LIMIT ..." statement.
     *
     * @param int
     * @param int Optional offset value
     * @return object
     */
    public function limit($limit, $offset = null){
	
	$this->limit_ = $limit;
	$this->offset_ = $offset;
	
	return $this;
    }
    
    /**
     * Build the SQL statement.
     *
     * @return string The complited SQL statement
     */
    public function _command(){
        
        $query = 'SELECT ';
	
	if($this->distinct_)
	    $query .= 'DISTINCT ';
        
        $column = '*';
        
        if( is_array($this->column) ){
            $column = implode(', ', $this->column);
	    unset($this->column);
        }
        
        $query .= $column;
        
        if( ! is_null($this->tables) )
            $query .= ' FROM '.$this->tables;
	
	if( ! is_null($this->joins) ) {
	    
	    if( ! is_null($this->joins_type) )
		$query .= ' '.strtoupper($this->joins_type);
	    
	    $query .= ' JOIN '.$this->joins;
	    
	    if( ! empty($this->joins_on) ){
		$query .= ' ON ('.implode(' ', $this->joins_on).')';
		unset($this->joins_on);
	    }
	}
	
	if( ! empty($this->criteria) ){
	    $query .= ' WHERE '.implode(' ', $this->criteria);
	    unset($this->criteria);
	}
	
	if( ! is_null($this->group_by_) )
	    $query .= ' GROUP BY '.$this->group_by_;
	    
	if( ! empty($this->is_having) ){
	    $query .= ' HAVING '.implode(' ', $this->is_having);
	    unset($this->is_having);
	}
	
	if( ! is_null($this->order_by_) )
	    $query .= ' ORDER BY '.$this->order_by_.' '.strtoupper($this->order_);
	
	
	if( ! is_null($this->limit_) ){
	    
	    $query .= ' LIMIT '.$this->limit_;
	    
	    if( ! is_null($this->offset_) )
		$query .= ' OFFSET '.$this->offset_;
	}
        
        return $query;
    }
    
    /**
     * Escape all unescaped string
     *
     * @param string $string
     * @return void
     */
    public function escape($string){
        
	if( is_null($this->link) )
	    $this->init();
	
        return $this->link->escapeString($string);
    }
    
    /**
     * Main function for querying to database
     *
     * @param $query The SQL querey statement
     * @return string|objet Return the resource id of query
     */
    public function query($sql){
	
	if( is_null($this->link) )
	    $this->init();
	
	if ( preg_match("/^(select)\s+/i", $sql) )
	    $query = $this->link->query($sql);
	else
	    $query = $this->link->exec($sql);
	
        $this->last_query = $sql;
        
	if($this->link->lastErrorMsg() != 'not an error' ){
	    $this->last_error = $this->link->lastErrorMsg();
            $this->print_error();
            return false;
        }
        
        return $query;
    }
    
    /**
     * Get multiple records
     *
     * @param string $query The sql query
     * @param string $type return data type option. the default is "object"
     */
    public function results($query = null, $type = 'object'){
        
	if( is_null($query) )
	    $query = $this->_command();
	
        $result = $this->query($query);
        
        while ( $row = $result->fetchArray(SQLITE3_ASSOC) ) {
            
            if($type == 'object')
                $return[] = (object) $row;
            else
                $return[] = $row;
        }
        
        return @$return;
    }
    
    /**
     * Get single record
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
        $return = $result->fetchArray(SQLITE3_ASSOC);
        
        if($type == 'object')
            return (object) $return;
        else
            return $return;
    }
    
    /**
     * Get value directly from single field
     *
     * @param string @query
     * @return string|int Depen on it record value.
     */
    public function get_var($query = null) {
        
	if( is_null($query) )
	    $query = $this->_command();
	
        $result = $this->row($query);
        $key = array_keys(get_object_vars($result));
        
        return $result->$key[0];
    }
    
    /**
     * Abstraction to get single record
     *
     * @param string
     * @param array Default si null
     * @param array Default is all
     * @return object
     */
    public function get_row($table, $where = array(), $fields = array()){
        
        if( ! empty($fields) )
	    call_user_func_array(array($this, 'select'), $fields);
	
	$this->from($table);
	
	if ( ! empty( $where ) ) {
	    
	    $separator = 'AND';
            foreach($where as $key => $val){
		
		if( end($where) == $val)
		    $separator = false;
		
		$this->where($key, '=', $val, $separator);
            }
        }
	
        return $this->row();
        
    }
    
    /**
     * Abstraction to get multyple records
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
	    
	    $separator = 'AND';
            foreach($where as $key => $val){
		
		if( end($where) == $val)
		    $separator = false;
		
		$this->where($key, '=', $val, $separator);
            }
        }
	
        return $this->results();
    }
    
    /**
     * Abstraction for insert
     *
     * @param string $table
     * @param array $data
     * @return boolean
     */
    public function insert($table, $data = array()) {
        
        $fields = array_keys($data);
        
        foreach($data as $key => $val)
            $escaped_date[$key] = $this->escape($val);
        
        return $this->query("INSERT INTO $table (" . implode(',',$fields) . ") VALUES ('".implode("','",$escaped_date)."')");
    }
    
    /**
     * Get the id form last insert
     *
     * @return int
     */
    public function insert_id(){
	
	if( is_null($this->link) )
	    $this->init();
	
        return $this->link->lastInsertRowID();
    }
    
    /**
     * Abstraction for update
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
     * Abstraction for delete
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
     * Print the error at least to PHP error log file
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
	
	$version = $this->link->version();
	return $version['versionString'];
    }
    
    /**
     * Close db connection
     *
     * @return void
     */
    public function close(){
	
	unset($this->link);
    }
    
} // End Driver_sqlite Class