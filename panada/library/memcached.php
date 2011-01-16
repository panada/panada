<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada Memcached API.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */

class Library_memcached extends Memcache {
    
    /**
     * EN: Load configuration from config file.
     * @return void
     */
    
    public function __construct(){
        
        $this->config = new Library_config();
        
        foreach($this->config->memcached_host as $host)
	    $this->addServer($host, $this->config->memcached_port);
	
	/**
	 * EN: If you need compression Threshold, you can uncomment this
	 */
	//$this->setCompressThreshold(20000, 0.2);
    }
} // End Library_memcached