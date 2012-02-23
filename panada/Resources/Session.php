<?php
/**
 * Panada session Handler.
 *
 * @package	Resources
 * @link	http://panadaframework.com/
 * @license	http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 * @since	Version 0.1
 */
namespace Resources;

class Session {
    
    private $driver, $config;
    
    public function __construct($connection = 'default'){
        
        $this->config = Config::session();
	$this->config = $this->config[$connection];
	$driverNamespace = 'Drivers\Session\\'.ucwords($this->config['driver']);
        $this->driver = new $driverNamespace($this->config);
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