<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada APC API Driver.
 *
 * @package	Driver
 * @subpackage	Cache
 * @author	Iskandar Soesman
 * @since	Version 0.2
 *
 * Install APC on Ubuntu: aptitude install libpcre3-dev;
 * pecl install apc
 */

/**
 * Makesure APC extension is enabled
 */
if( ! extension_loaded('apc') )
    Library_error::_500('APC extension that required by Library_apc is not available.');
    
class Drivers_cache_apc {
    
    public function __construct(){
        // none
    }
    
    /**
     * PHP Magic method for calling a method dinamicly
     * 
     * @param string $name
     * @param mix $arguments
     * @return mix
     */
    public function __call($name, $arguments){
        
        return call_user_func_array($name, $arguments);
    }
    
    /**
     * PHP Magic method for calling a static method dinamicly
     * 
     * @param string $name
     * @param mix $arguments
     * @return mix
     */
    public static function __callStatic($name, $arguments) {
        
        return call_user_func_array($name, $arguments);
    }
    
    /**
     * @param string $key
     * @param mix $value
     * @param int $expire
     * @return void
     */
    public function set( $key, $value, $expire = 0 ){
        
        return $this->apc_add($key, $value, $expire); 
    }
    
    /**
     * @param string $key
     * @return mix
     */
    public function get( $key ){
        
        return $this->apc_fetch($key); 
    }
    
    /**
     * @param string $key
     * @return void
     */
    public function delete( $key ){
        
        return $this->apc_delete($key);
    }
} // End Drivers_cache_apc