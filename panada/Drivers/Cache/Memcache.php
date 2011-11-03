<?php
/**
 * Panada Memcached API Driver.
 *
 * @package	Driver
 * @subpackage	Cache
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */

/**
 * EN: Makesure Memcache extension is enabled
 */
namespace Drivers\Cache;

if( ! \extension_loaded('memcache') )
    die('Memcache extension that required by Library_memcached is not available.');

class Memcache extends \Memcache {
    
    public $port = 11211;
    
    /**
     * EN: Load configuration from config file.
     * @return void
     */
    
    public function __construct( $config_instance ){
        
	$config_instance->host = (array) $config_instance->host;
	
        foreach($config_instance->host as $host)
	    $this->addServer($host, $this->port);
	
	/**
	 * EN: If you need compression Threshold, you can uncomment this
	 */
	//$this->setCompressThreshold(20000, 0.2);
    }
    
    /**
     * @param string $key
     * @param mix $value
     * @param int $expire
     * @param string $namespace
     * @return void
     */
    public function set_value( $key, $value, $expire = 0, $namespace = false ){
        
	$key = $this->key_to_namespace($key, $namespace);
        return $this->set($key, $value, 0, $expire);
    }
    
    /**
     * Cached the value if the key doesn't exists,
     * other wise will false.
     *
     * @param string $key
     * @param mix $value
     * @param int $expire
     * @param string $namespace
     * @return void
     */
    public function add_value( $key, $value, $expire = 0, $namespace = false ){
        
	$key = $this->key_to_namespace($key, $namespace);
	return $this->add($key, $value, 0, $expire); 
    }
    
    /**
     * Update cache value base on the key given.
     *
     * @param string $key
     * @param mix $value
     * @param int $expire
     * @param string $namespace
     * @return void
     */
    public function update_value( $key, $value, $expire = 0, $namespace = false ){
        
	$key = $this->key_to_namespace($key, $namespace);
	return $this->replace($key, $value, 0, $expire);
    }
    
    /**
     * @param string $key
     * @param string $namespace
     * @return mix
     */
    public function get_value( $key, $namespace = false ){
        
	$key = $this->key_to_namespace($key, $namespace);
        return $this->get($key);
    }
    
    /**
     * @param string $key
     * @param string $namespace
     * @return void
     */
    public function delete_value( $key, $namespace = false ){
        
	$key = $this->key_to_namespace($key, $namespace);
        return $this->delete($key);
    }
    
    /**
     * Flush all cached object.
     * @return bool
     */
    public function flush_values(){
        
	return $this->flush();
    }
    
    /**
     * Namespace usefull when we need to wildcard deleting cache object.
     *
     * @param string $namespace_key
     * @return int Unixtimestamp
     */
    private function key_to_namespace( $key, $namespace_key = false ){
	
	if( ! $namespace_key )
	    return $key;
	
	if( ! $namespace_value = $this->get($namespace_key) ){
	    $namespace_value = time();
	    $this->set($namespace_key, $namespace_value, 0, 0);
	}
	
	return $namespace_value.'_'.$key;
    }
    
} // End Drivers_cache_memcache