<?php defined('THISPATH') or die('Tidak diperkenankan mengakses file secara langsung.');
/**
 * Panada MySQL Database API.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman. Inspired by ezSQL {@link http://php.justinvincent.com Justin Vincent (justin@visunet.ie)}
 * @since	Version 0.1
 */

class Library_db {
    
    var $link;
    var $insert_id;
    var $last_query;
    var $last_error;
    
    /**
     * EN: Define all properties needed.
     * @return void
     */
    function __construct($connection = 'default'){
        
        $db_config = $GLOBALS['CONFIG']['db'][$connection];
        $this->link = @mysql_connect($db_config['host'], $db_config['user'], $db_config['password']);
        
        if ( ! $this->link )
            library_error::database('Unable connet to database.');
            
        $this->select_db($db_config['database']);
    }
    
    /**
     * EN: Select the databse
     *
     * @return void
     */
    function select_db($dbname){
        
        if ( ! @mysql_select_db($dbname, $this->link) )
            library_error::database('Unable to select database.');
        
    }
    
    /**
     * EN: Escape all unescaped string
     *
     * @param string $string
     * @return void
     */
    function escape($string){
        
        return mysql_real_escape_string($string, $this->link);
    }
    
    /**
     * EN: Main function for querying to database
     *
     * @param $query The SQL querey statement
     * @return string|objet Return the resource id of query
     */
    function query($sql){
        
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
    function results($query, $type = 'object'){
        
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
    function row($query, $type = 'object'){
        
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
    function get_var($query) {
        
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
    function get($table, $where = array(), $fields = array()){
        
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
    function get_row($table, $where = array(), $fields = array()){
        
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
    function get_results($table, $where = array(), $fields = array()){
        
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
    function insert($table, $data = array()) {
        
        $fields = array_keys($data);
        
        foreach($data as $key => $val)
            $escaped_date[$key] = $this->escape($val);
        
        return $this->query("INSERT INTO `$table` (`" . implode('`,`',$fields) . "`) VALUES ('".implode("','",$escaped_date)."')");
    }
    
    /**
     * EN: Abstraction for update
     *
     * @param string $table
     * @param array $dat
     * @param array $where
     * @return boolean
     */
    function update($table, $dat, $where){
        
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
    function delete($table, $where){
        
        if ( is_array( $where ) )
            foreach ( $where as $c => $v )
                $wheres[] = "$c = '" . $this->escape( $v ) . "'";
        else
            return false;
        
        return $this->query( "DELETE FROM `$table` WHERE " . implode( ' AND ', $wheres ) );
    }
    
    /**
     * EN: Get what function just call the query. This in for debugging purepose.
     *
     * @return string
     */
    function get_caller() {
        
        $bt = debug_backtrace();
        $caller = array();

        $bt = array_reverse( $bt );
        foreach ( (array) $bt as $call ) {
            if ( @$call['class'] == __CLASS__ )
                continue;
            $function = $call['function'];
            if ( isset( $call['class'] ) )
                $function = $call['class'] . "->$function";
            $caller[] = $function;
        }
        $caller = join( ', ', $caller );

        return $caller;
    }
    
    /**
     * EN: Print the error at least to PHP error log file
     *
     * @return string
     */
    function print_error() {
    
        if ( $caller = $this->get_caller() )
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
        library_error::database($str.'<br />'.$query);
    }
    
}// End library_mysql