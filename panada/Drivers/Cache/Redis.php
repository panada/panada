<?php
/**
 * Panada Redis API Driver.
 *
 * @package	Driver
 * @subpackage	Cache
 * @author	Iskandar Soesman
 * @since	Version 1.0
 */

/**
 * Makesure Memcache extension is enabled
 */
namespace Drivers\Cache;
use Resources\Interfaces as Interfaces;

class Redis extends \Redis implements Interfaces\Cache {
    
    private $port = 6379;
    
    public function __construct( $config ){
	
	if( ! extension_loaded('redis') )
	    die('Redis extension that required by Driver Redis is not available.');
	
        parent::__construct();
        
        foreach($config['server'] as $server){
            
            if ( $server['persistent']) {
                $this->pconnect($server['host'], $server['port'], $server['timeout']);
            }
            else {
                $this->connect($server['host'], $server['port'], $server['timeout']);
            }
        }
        
        $this->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
    }
    
    /**
     * @param string $key
     * @param mix $value
     * @param int $expire
     * @param string $namespace
     * @return void
     */
    public function setValue( $key, $value, $expire = 0, $namespace = false ){
        
	$key = $this->keyToNamespace($key, $namespace);
        return $this->set($key, $value, $expire);
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
    public function addValue( $key, $value, $expire = 0, $namespace = false ){
        
	$key = $this->keyToNamespace($key, $namespace);
	return $this->setnx($key, $value, $expire); 
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
    public function updateValue( $key, $value, $expire = 0, $namespace = false ){
        
	$key = $this->keyToNamespace($key, $namespace);
	return $this->setValue($key, $value, $expire);
    }
    
    /**
     * @param string $key
     * @param string $namespace
     * @return mix
     */
    public function getValue( $key, $namespace = false ){
        
	$key = $this->keyToNamespace($key, $namespace);
        return $this->get($key);
    }
    
    /**
     * @param string $key
     * @param string $namespace
     * @return void
     */
    public function deleteValue( $key, $namespace = false ){
        
	$key = $this->keyToNamespace($key, $namespace);
        return $this->delete($key);
    }
    
    /**
     * Flush all cached object.
     * @return bool
     */
    public function flushValues(){
        
	return $this->flushDB();
    }
    
    /**
     * Namespace usefull when we need to wildcard deleting cache object.
     *
     * @param string $namespace_key
     * @return int Unixtimestamp
     */
    private function keyToNamespace( $key, $namespace_key = false ){
	
	if( ! $namespace_key )
	    return $key;
	
	if( ! $namespace_value = $this->get($namespace_key) ){
	    $namespace_value = time();
	    $this->set($namespace_key, $namespace_value, 0);
	}
	
	return $namespace_value.'_'.$key;
    }
    
}