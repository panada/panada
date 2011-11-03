<?php
/**
 * Panada Database session hendler.
 *
 * @package	Driver
 * @subpackage	Session
 * @author	Iskandar Soesman
 * @since	Version 0.3
 */
namespace Dirvers\Session;

// Load the Drivers_session_native class for inheritance.
require_once 'native.php';

class Database extends \Native {
    
    /**
     * @var string	EN: Session table name.
     *			ID: Nama table session.
     */
    private $session_db_name = 'sessions';
    private $session_db_conn;
    
    public function __construct( $config_instance ){
        
	$this->session_db_name	= $config_instance->storage_name;
        $this->session_db_conn	= new Library_db( $config_instance->driver_connection );
        
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
	
	$session = $this->session_db_conn->select('session_data')->from( $this->session_db_name )->where('session_id', '=', $id, 'and')->where('session_expiration', '>', time())->find_one();
	
	if( $session )
	    return $session->session_data;
	
	return false;
    }
    
    /**
     * EN: Get session data by session id
     *
     * @param string
     * @return int
     */
    private function session_exist($id){
	
	$session = $this->session_db_conn->select('session_data', 'session_expiration')->from( $this->session_db_name )->where('session_id', '=', $id)->find_one();
	return $session;
    }
    
    /**
     * EN: Write the session data
     *
     * @param string
     * @param string
     * @return boolean
     */
    public function session_write($id, $sess_data){
	
	$curent_session = $this->session_exist($id);
	$expiration	= $this->upcoming_time($this->sesion_expire);
	
	if( $curent_session ){
	    
	    if( (md5($curent_session->session_data) == md5($sess_data)) && ($curent_session->session_expiration > time() + 10 ) )
		return true;
	   
	    return $this->session_db_conn->update($this->session_db_name, array('session_id' => $id, 'session_data' => $sess_data, 'session_expiration' => $expiration), array('session_id' => $id) ); 
	}
	else{
	    
	    return $this->session_db_conn->insert($this->session_db_name, array('session_id' => $id, 'session_data' => $sess_data, 'session_expiration' => $expiration)); 
	}
    }
    
    /**
     * EN: Remove session data
     *
     * @param string
     * @return boolean
     */
    public function session_destroy($id){
	
	return $this->session_db_conn->delete($this->session_db_name, array('session_id' => $id));
    }
    
    /**
     * EN: Clean all expired record in db trigered by PHP Session Garbage Collection
     *
     * @param date I don't think we still need this parameter since the expired date was store in db.
     * @return boolean
     */
    public function session_gc($maxlifetime = ''){
	
	return $this->session_db_conn->where( 'session_expiration', '<', time() )->delete( $this->session_db_name );
    }
    
} // End Drivers_session_database