<?php
/**
 * Panada Database API.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman.
 * @since	Version 0.3
 */
namespace Resources;

class Database {
    
    private $driver;
    private $config;
    
    public function __construct( $connection = 'default' ){
        
        $this->config = Library_config::instance();
        
        require_once GEAR.'drivers/database/'.$this->config->db->$connection->driver.'.php';
        
        $class_name = 'Drivers_database_'.$this->config->db->$connection->driver;
        
        $this->driver = new $class_name( $this->config->db->$connection, $connection );
    }
    
    /**
     * Use magic method 'call' to pass user method
     * into driver method
     *
     * @param string @name
     * @param array @arguments
     */
    public function __call($name, $arguments){
        
        return call_user_func_array(array($this->driver, $name), $arguments);
    }
    
    public function __get($name){
        
        return $this->driver->$name;
    }
    
    public function __set($name, $value){
        
        $this->driver->$name = $value;
    }
    
} // End Class Library_database