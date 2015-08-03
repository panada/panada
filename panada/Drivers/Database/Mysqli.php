<?php
/**
 * Panada MySQLi Database Driver.
 *
 * @package	Driver
 * @subpackage	Database
 * @author	Iskandar Soesman.
 * @since	Version 1.0
 */
namespace Drivers\Database;
use Resources\RunException as RunException;

class Mysqli extends \Drivers\Abstraction\Sql implements \Resources\Interfaces\Database
{    
    protected $port = 3306;
    
    /**
     * Define all properties needed.
     * @return void
     */
    function __construct( $config, $connectionName )
    {
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
	if($this->config['persistent'])
	    $this->config['host'] = 'p:'.$this->config['host'];
	
        return mysqli_connect($this->config['host'], $this->config['user'], $this->config['password'], $this->config['database'], $this->config['port']);
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
        
        $collation_query = '';
        
        if ( ! empty($this->config['charset']) ) {
            $collation_query = "SET NAMES '".$this->config['charset']."'";
	    if ( ! empty($this->config['collate']) )
                $collation_query .= " COLLATE '".$this->config['collate']."'";
	}
	
        if ( ! empty($collation_query) )
            $this->query($collation_query);
        
    }
    
    /**
     * Select the databse
     *
     * @return void
     */
    private function selectDb($dbname)
    {
	if( is_null($this->link) )
	    $this->init();
        
	try{
	    if ( ! mysqli_select_db($this->link, $dbname) )
		throw new RunException('Unable to select database in <strong>'.$this->connection.'</strong> connection.');
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
	$this->query("SET AUTOCOMMIT=0");
	$this->query("START TRANSACTION");       
    }
    
    /**
     * Commit transaction.
     *
     * @return void
     */
    public function commit()
    {
	$this->query("COMMIT");
	$this->query("SET AUTOCOMMIT=1");
    }
    
    /**
     * Rollback transaction.
     *
     * @return void
     */
    public function rollback()
    {
	$this->query("ROLLBACK");
	$this->query("SET AUTOCOMMIT=1");
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
	
        return mysqli_real_escape_string($this->link, $string);
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
        
        $query = mysqli_query($this->link, $sql);
        $this->lastQuery = $sql;
        
        if ( $this->lastError = mysqli_error($this->link) ) {
            
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
     * @param string $returnType return data type option: object, array and iterator (pointer)
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
	    
	    while ($row = mysqli_fetch_object($result, $this->instantiateClass))
		$return[] = $row;
	    
	    mysqli_free_result($result);
	    
	    return $return;
	}
	
	if($this->returnType == 'iterator')
	    return $result;
	
	if($this->returnType == 'array') {
	    
	    while ($row = mysqli_fetch_assoc($result))
		$return[] = $row;
	    
	    mysqli_free_result($result);
	    
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
        $return = mysqli_fetch_object($result, $this->instantiateClass);
        
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
	return mysqli_insert_id($this->link);
    }
    
    /**
     * Get this db version
     *
     * @return void
     */
    public function version()
    {
	return $this->getVar("SELECT version() AS version");
    }
    
    /**
     * Close db connection
     *
     * @return void
     */
    public function close()
    {
	mysqli_close($this->link);
    }
    
}