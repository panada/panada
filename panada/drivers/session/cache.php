<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada Objet Cache session hendler.
 *
 * @package	Driver
 * @subpackage	Session
 * @author	Iskandar Soesman
 * @since	Version 0.3
 */

// Load the Drivers_session_native class for inheritance.
require_once 'native.php';

class Drivers_session_cache extends Drivers_session_native {
    
    private $session_storage_name = 'sessions_';
    
    public function __construct( $config_instance ){
        
	$this->session_storage_name = $config_instance->storage_name.'_';
        $this->cache		    = new Library_cache( $config_instance->driver_connection );
        
        session_set_save_handler (
	    array($this, 'session_start'),
	    array($this, 'session_end'),
	    array($this, 'session_read'),
	    array($this, 'session_write'),
	    array($this, 'session_destroy'),
	    array($this, 'session_gc')
	);
        
        parent::__construct( $config_instance );
    }
    
    /**
     * EN: Required function for session_set_save_handler act like constructor in a class
     *
     * @param string
     * @param string
     * @return void
     */
    public function session_start($save_path, $session_name){
	//EN: We don't need anythings at this time.
    }
    
    /**
     * EN: Required function for session_set_save_handler act like destructor in a class
     *
     * @return void
     */
    public function session_end(){
	//EN: we also don't have do anythings too!
    }
    
    /**
     * EN: Read session from db or file
     *
     * @param string $id The session id
     * @return string|array|object|boolean
     */
    public function session_read($id){
        
        return $this->cache->get_value($this->session_storage_name.$id);
    }
    
    /**
     * EN: Write the session data
     *
     * @param string
     * @param string
     * @return boolean
     */
    public function session_write($id, $sess_data){
	
	if( $this->session_read($id) )
            return $this->cache->update_value($this->session_storage_name.$id, $sess_data, $this->sesion_expire);
	else
            return $this->cache->set_value($this->session_storage_name.$id, $sess_data, $this->sesion_expire);
    }
    
    /**
     * EN: Remove session data
     *
     * @param string
     * @return boolean
     */
    public function session_destroy($id){
	
	return $this->cache->delete_value($this->session_storage_name.$id);
    }
    
    /**
     * Clean all expired record in db trigered by PHP Session Garbage Collection.
     * All cached session object will automaticly removed by the cache service, so we
     * dont have to do anythings.
     *
     * @return void
     */
    public function session_gc($maxlifetime = 0){
	//none
    }
    
} // End Drivers_session_cache