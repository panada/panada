<?php
/**
 * Panada MySQL Database Driver.
 *
 * @package	Driver
 * @subpackage	Database
 * @author	Iskandar Soesman.
 * @since	Version 0.1
 */
namespace Drivers\Database;
use Resources\RunException as RunException;

class Mysql extends \Drivers\Abstraction\Sql implements \Resources\Interfaces\Database
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
	$function = ($this->config['persistent']) ? 'mysql_pconnect' : 'mysql_connect';
	
	return $function(
		    $this->config['host'].':'.$this->config['port'],
		    $this->config['user'],
		    $this->config['password'],
		    $this->newLink,
		    $this->clientFlags
		);
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
        
        $this->selectDb($this->config['database']);
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
	    if ( ! @mysql_select_db($dbname, $this->link) )
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
	$this->query("START TRANSACTION");
	$this->query("BEGIN");       
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
	
        return mysql_real_escape_string($string, $this->link);
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
        
        $query = mysql_query($sql, $this->link);
        $this->lastQuery = $sql;
        
        if ( $this->lastError = mysql_error($this->link) ) {
	    
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
	    
	    while ($row = mysql_fetch_object($result, $this->instantiateClass))
		$return[] = $row;
	    
	    mysql_free_result($result);
	    
	    return $return;
	}
	
	if($this->returnType == 'iterator')
	    return $result;
	
	if($this->returnType == 'array') {
	    
	    while ($row = mysql_fetch_assoc($result))
		$return[] = $row;
	    
	    mysql_free_result($result);
	    
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
        $return = mysql_fetch_object($result, $this->instantiateClass);
        
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
	return @mysql_insert_id($this->link);
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
	return $this->getVar("SELECT version() AS version");
    }
    
    /**
     * Close db connection
     *
     * @return void
     */
    public function close()
    {
	mysql_close($this->link);
    }
    
}