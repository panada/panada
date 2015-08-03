<?php
/**
 * Panada Cubrid API.
 *
 * @package	Driver
 * @subpackage	Database
 * @author	Iskandar Soesman
 * @since	Version 1.0
 */
namespace Drivers\Database;
use Resources\RunException as RunException;

class Cubrid extends \Drivers\Abstraction\Sql implements \Resources\Interfaces\Database
{    
    protected $port = 33000;
    
    /**
     * Define all properties needed.
     * @return void
     */
    function __construct( $config, $connectionName )
    {    
        if( ! \extension_loaded('cubrid') )
           die('Cubrid extension that required by Cubrid Driver is not available.');
	
	$this->config = $config;
	$this->connection = $connectionName;
    }
    
    /**
     * Establish a new connection to mysql server
     *
     * @return string | boolean MySQL persistent link identifier on success, or FALSE on failure.
     */
    private function establishConnection()
    {
	$function = ( $this->config['persistent'] ) ? 'cubrid_pconnect' : 'cubrid_connect';
        
        $conn = $function($this->config['host'], $this->config['port'], $this->config['database'], $this->config['user'], $this->config['password']);
        
        if ($conn){
            if (isset($this->auto_commit) && !$this->auto_commit){
                cubrid_set_autocommit($conn, CUBRID_AUTOCOMMIT_FALSE);
            }
            else{
                cubrid_set_autocommit($conn, CUBRID_AUTOCOMMIT_TRUE);
                $this->auto_commit = TRUE;
            }
        }
        
        return $conn;
    }
    
    /**
     * Inital for all process
     *
     * @return void
     */
    private function init()
    {
	if( is_null($this->link) )
	    $this->link = $this->establishConnection();
        
	try{
	    if ( ! $this->link )
		throw new RunException('Unable connect to database in <strong>'.$this->connection.'</strong> connection.');
	}
	catch(RunException $e){
	    RunException::outputError( $e->getMessage() );
	}
    }
    
    /**
     * Start transaction.
     *
     * @return void
     */
    public function begin()
    {
	if (cubrid_get_autocommit($this->link))
            cubrid_set_autocommit($this->link, CUBRID_AUTOCOMMIT_FALSE);
    }
    
    /**
     * Commit transaction.
     *
     * @return void
     */
    public function commit()
    {
	cubrid_commit($this->link);
        
        if ($this->autoCommit && !cubrid_get_autocommit($this->link))
            cubrid_set_autocommit($this->link, CUBRID_AUTOCOMMIT_TRUE);
    }
    
    /**
     * Rollback transaction.
     *
     * @return void
     */
    public function rollback()
    {    
	cubrid_rollback($this->link);
        
        if ($this->autoCommit && !cubrid_get_autocommit($this->link))
            cubrid_set_autocommit($this->link, CUBRID_AUTOCOMMIT_TRUE);
    }
    
    /**
     * Escape all unescaped string
     *
     * @param string $string
     * @return void
     */
    public function escape($string)
    {    
	if( is_null($this->link) )
	    $this->init();
        
        if (function_exists('cubrid_real_escape_string'))
            $string = cubrid_real_escape_string($string, $this->link);
        else
            $string = addslashes($string);
        
        return $string;
    
    }
    
    /**
     * Main function for querying to database
     *
     * @param $query The SQL querey statement
     * @return string|objet Return the resource id of query
     */
    public function query($sql)
    {
	if( is_null($this->link) )
	    $this->init();
        
        $query = cubrid_query($sql, $this->link);
        $this->lastQuery = $sql;
        
        if ( $this->lastError = cubrid_error($this->link) ) {
            
	    if( $this->throwError ) {
		throw new \Exception($this->lastError);
	    }
	    else {
		$this->printError();
		return false;
	    }
        }
        
        return $query;
    }
    
    /**
     * Get multiple records
     *
     * @param string $query The sql query
     * @param string $type return data type option. the default is "object"
     */
    public function results($query, $returnType = false)
    {
	$return = false;
	
	if($returnType)
	    $this->returnType = $returnType;
	
	if( is_null($query) )
	    $query = $this->command();
	
        $result = $this->query($query);
	
	if($this->returnType == 'object') {
	    
	    while ($row = cubrid_fetch_object($result, $this->instantiateClass))
		$return[] = $row;
	    
	    cubrid_close_request($result);
	    
	    return $return;
	}
	
	if($this->returnType == 'iterator')
	    return $result;
	
	if($this->returnType == 'array') {
	    
	    while ($row = cubrid_fetch_assoc($result))
		$return[] = $row;
	    
	    cubrid_close_request($result);
	    
	    return $return;
	}
	
        return $return;
    }
    
    /**
     * Get single record
     *
     * @param string $query The sql query
     * @param string $type return data type option. the default is "object"
     */
    public function row($query, $returnType = false)
    {
	if($returnType)
	    $this->returnType = $returnType;
	
	if( is_null($query) )
	    $query = $this->command();
	
	if( is_null($this->link) )
	    $this->init();
        
        $result = $this->query($query);
        $return = cubrid_fetch_object($result, $this->instantiateClass);
        
        if($this->returnType == 'object')
            return $return;
	
	return (array) $return;
    }
    
    /**
     * Get the id form last insert
     *
     * @return int
     */
    public function insertId()
    {
	return cubrid_insert_id($this->link);
    }
    
    /**
     * Abstraction for replace
     *
     * @param string $table
     * @param array $data
     * @return boolean
     */
    public function replace($table, $data = array())
    {    
        $fields = array_keys($data);
        
        foreach($data as $key => $val)
            $escaped_date[$key] = $this->escape($val);
        
        return $this->query("REPLACE INTO `$table` (`" . implode('`,`',$fields) . "`) VALUES ('".implode("','",$escaped_date)."')");
    }
    
    /**
     * Get this db version
     *
     * @return void
     */
    public function version()
    {
	return cubrid_get_server_info($this->link);
    }
    
    /**
     * Close db connection
     *
     * @return void
     */
    public function close()
    {
	cubrid_close($this->link);
    }
}