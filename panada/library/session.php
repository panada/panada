<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada session hendler.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */

class Library_session {
    
    private $driver;
    private $config;
    
    public function __construct(){
        
        $this->config = Library_config::instance();
	$this->config->session->secret_key = $this->config->secret_key;
        
        require_once GEAR.'drivers/session/'.$this->config->session->driver.'.php';
        
        $class_name = 'Drivers_session_'.$this->config->session->driver;
        
        $this->driver = new $class_name($this->config->session);
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