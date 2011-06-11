<?php defined('THISPATH') or die('Can\'t access directly!');
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
if( ! extension_loaded('memcache') )
    Library_error::_500('Memcache extension that required by Library_memcached is not available.');

class Drivers_cache_memcached extends Memcache {
    
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
} // End Drivers_cache_memcached