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
    
    private $driver, $config;
    
    public function __construct( $connection = 'default' ){
        
        $config         = Config::database();
        $this->config   = $config[$connection];
        
        $driverNamespace = 'Drivers\Database\\'.ucwords($this->config['driver']);
        
        $this->driver = new $driverNamespace( $this->config, $connection );
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
    
}