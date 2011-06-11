<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada cache API.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.3
 */

class Library_cache {
    
    private $driver;
    private $config;
    
    public function __construct( $connection = 'default' ){
        
        $this->config = Library_config::instance();
        
        require_once GEAR.'drivers/cache/'.$this->config->cache->$connection->driver.'.php';
        
        $class_name = 'Drivers_cache_'.$this->config->cache->$connection->driver;
        
        $this->driver = new $class_name( $this->config->cache->$connection );
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
    
    /**
     * PHP Magic method for calling a class property dinamicly
     * 
     * @param string $name
     * @return mix
     */
    public function __get($name){
        
        return $this->driver->$name;
    }
    
    /**
     * PHP Magic method for set a class property dinamicly
     * 
     * @param string $name
     * @param mix $value
     * @return void
     */
    public function __set($name, $value){
        
        $this->driver->$name = $value;
    }
} // End Library_cache