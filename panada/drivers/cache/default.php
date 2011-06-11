<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada Local Memory Cacher.
 * This class useful when you calling an object twice or
 * more in a single run time.
 *
 * @package	Driver
 * @subpackage	Cache
 * @author	Iskandar Soesman
 * @since	Version 0.3
 */

class Drivers_cache_default {
    
    static private $holder = array();
    
    public function __construct(){
        // none
    }
    
    /**
     * @param string $key
     * @return mix
     */
    public function get($key){
        
        return self::_get($key);
    }
    
    /**
     * @param string $key
     * @param mix
     * @return void
     */
    public function set($key, $value){
        
        return self::_set($key, $value);
    }
    
    /**
     * @param string $key
     * @return void
     */
    public function delete($key){
        
        return self::_delete($key);
    }
    
    /**
     * @param string $key
     * @return mix
     */
    public static function _get($key = false){
        
        if( isset(self::$holder[$key]) )
            return self::$holder[$key];
        
        return false;
    }
    
    /**
     * @param string $key
     * @param mix
     * @return void
     */
    public static function _set($key, $value){
        
        self::$holder[$key] = $value;
    }
    
    /**
     * @param string $key
     * @return void
     */
    public static function _delete($key){
        
        unset(self::$holder[$key]);
    }

} // End Library_local_memory_cache