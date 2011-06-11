<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada Local Memory Cacher.
 * This class useful when you calling an object twice or
 * more in a single run time.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.3
 */

class Library_local_memory_cache {
    
    static private $holder = array();
    
    /**
     * @param string $key
     * @return mix
     */
    public static function get($key = false){
        
        if( isset(self::$holder[$key]) )
            return self::$holder[$key];
        
        return false;
    }
    
    /**
     * @param string $key
     * @param mix
     * @return void
     */
    public static function set($key, $value){
        
        self::$holder[$key] = $value;
    }
    
    /**
     * @param string $key
     * @return void
     */
    public static function delete($key){
        
        unset(self::$holder[$key]);
    }

} // End Library_local_memory_cache