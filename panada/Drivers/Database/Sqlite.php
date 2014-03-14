<?php
/**
 * Panada SQLite Database Driver.
 *
 * @package	Driver
 * @subpackage	Database
 * @author	Iskandar Soesman.
 * @since	Version 0.3
 */
namespace Drivers\Database;
use Resources\RunException as RunException;
use Resources\Tools as Tools;

class Sqlite extends \Drivers\Abstraction\Sql implements \Resources\Interfaces\Database
{
    /**
     * EN: Define all properties needed.
     * @return void
     */
    function __construct( $config, $connectionName )
    {
	$this->config = $config;
	$this->connection = $connectionName;
	
    }
    
    /**
     * Establish a new connection to SQLite server
     *
     */
    private function establishConnection()
    {
	try{
	    if( ! $this->link = new \SQLite3( $this->config['database'], SQLITE3_OPEN_READWRITE ) )
		throw new RunException('Unable connect to database in <strong>'.$this->connection.'</strong> connection.');
	}
	catch(RunException $e){
	    RunException::outputError( $e->getMessage() );
	}
	
    }
    
    /**
     * Inital for all process
     *
     * @return void
     */
    private function init()
    {
	if( is_null($this->link) )
	    $this->establishConnection();
    }
    
    /**
     * Start transaction.
     *
     * @return void
     */
    public function begin()
    {
	$this->query("BEGIN TRANSACTION");
    }
    
    /**
     * Commit transaction.
     *
     * @return void
     */
    public function commit()
    {
	$this->query("COMMIT");
    }
    
    /**
     * Rollback transaction.
     *
     * @return void
     */
    public function rollback()
    {
	$this->query("ROLLBACK");
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
	
        return $this->link->escapeString($string);
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
	
	if ( preg_match("/^(select)\s+/i", $sql) )
	    $query = $this->link->query($sql);
	else
	    $query = $this->link->exec($sql);
	
        $this->lastQuery = $sql;
        
	if($this->link->lastErrorMsg() != 'not an error' ){
	    
	    $this->lastError = $this->link->lastErrorMsg();
	    
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
    public function results($query = null, $returnType = false)
    {
	if($returnType)
	    $this->returnType = $returnType;
	
	if( is_null($query) )
	    $query = $this->command();
	
        $result = $this->query($query);
        
	if( $result->numColumns() < 1 )
	    return false;
	
	while ( $row = $result->fetchArray(SQLITE3_ASSOC) )
	    $return[] = $row;
	
	if($this->returnType == 'object')
	    return Tools::arrayToObject($return, $this->instantiateClass, false);
	
	if($this->returnType == 'iterator')
	    return $result;
	
	if($this->returnType == 'array')
	    return $return;
	
        return false;
    }
    
    /**
     * Get single record
     *
     * @param string $query The sql query
     * @param string $type return data type option. the default is "object"
     */
    public function row($query = null, $returnType = false)
    {
	if($returnType)
	    $this->returnType = $returnType;
	
	if( is_null($query) )
	    $query = $this->command();
	
	if( is_null($this->link) )
	    $this->init();
        
        $result = $this->query($query);
	
        if( ! $return = $result->fetchArray(SQLITE3_ASSOC) )
	    return false;
        
        if($this->returnType == 'object')
	    return Tools::arrayToObject($return, $this->instantiateClass, false);
        
        return $return;
    }
    
    /**
     * Get the id form last insert
     *
     * @return int
     */
    public function insertId()
    {
	if( is_null($this->link) )
	    $this->init();
	
        return $this->link->lastInsertRowID();
    }
    
    /**
     * Get this db version
     *
     * @return void
     */
    public function version()
    {
	$version = $this->link->version();
	return $version['versionString'];
    }
    
    /**
     * Close db connection
     *
     * @return void
     */
    public function close()
    {
	unset($this->link);
    }
    
}