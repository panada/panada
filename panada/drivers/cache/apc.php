<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada APC API.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.2
 *
 * Install APC on Ubuntu: aptitude install libpcre3-dev;
 * pecl install apc
 */

/**
 * ID: Pastikan ekstensi APC telah terinstall
 * EN: Makesure APC extension is enabled
 */
if( ! extension_loaded('apc') )
    Library_error::_500('APC extension that required by Library_apc is not available.');
    
class Library_apc {
    
    public function __construct(){
        
    }
    
    public function __call($name, $arguments){
        
        return call_user_func_array($name, $arguments);
    }
    
    public static function __callStatic($name, $arguments) {
        
        return call_user_func_array($name, $arguments);
    }
}