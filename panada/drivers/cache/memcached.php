<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada Memcached API.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */

/**
 * ID: Pastikan ektensi Memcache telah terinstall
 * EN: Makesure Memcache extension is enabled
 */
if( ! extension_loaded('memcache') )
    Library_error::_500('Memcache extension that required by Library_memcached is not available.');

class Library_memcached extends Memcache {
    
    /**
     * EN: Load configuration from config file.
     * @return void
     */
    
    public function __construct(){
        
        $this->config = Library_config::instance();
        
        foreach($this->config->memcached_host as $host)
	    $this->addServer($host, $this->config->memcached_port);
	
	/**
	 * EN: If you need compression Threshold, you can uncomment this
	 */
	//$this->setCompressThreshold(20000, 0.2);
    }
} // End Library_memcached