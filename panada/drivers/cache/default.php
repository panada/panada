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
     * @param mix $value
     * @param int $expire
     * @return void
     */
    public function set_value($key, $value, $expire = 0){
        
        return self::_set($key, $value);
    }
    
    /**
     * Cached the value if the key doesn't exists,
     * other wise will false.
     *
     * @param string $key
     * @param mix $value
     * @param int $expire
     * @return void
     */
    public function add_value( $key, $value, $expire = 0 ){
        
        return self::_get($key) ? false : self::_set($key, $value);
    }
    
    /**
     * Update cache value base on the key given.
     *
     * @param string $key
     * @param mix $value
     * @param int $expire
     * @return void
     */
    public function update_value( $key, $value, $expire = 0 ){
        
        return self::_set($key, $value);
    }
    
    /**
     * @param string $key
     * @return mix
     */
    public function get_value($key){
        
        return self::_get($key);
    }
    
    /**
     * @param string $key
     * @return void
     */
    public function delete_value($key){
        
        return self::_delete($key);
    }
    
    /**
     * Flush all cached object.
     * @return bool
     */
    public function flush_values(){
        
        return self::_flush();
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
    
    public static function _flush(){
        
        unset(self::$holder);
        return true;
    }

} // End Library_local_memory_cache