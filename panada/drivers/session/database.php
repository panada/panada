<?php defined('THISPATH') or die('Can\'t access directly!');

require_once 'native.php';

class Drivers_session_database extends Driver_session_native {
    
    /**
     * @var string	EN: Session table name.
     *			ID: Nama table session.
     */
    private $session_db_name = 'sessions';
    
    public function __construct( $config_instance ){
        
	$this->session_db_name	= $config_instance->storage_name;
        $this->db		= new Library_db();
        
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
	
	$sql = "SELECT session_data FROM $this->session_db_name
		WHERE session_id ='$id' AND session_expiration > ".time().")";
	
	if( $session = $this->db->row($sql) )
	    return $session->session_data;
	else
	    return false;
    }
    
    /**
     * EN: Get session data by session id
     *
     * @param string
     * @return int
     */
    private function session_exist($id){
	
	$session = $this->db->find_var("SELECT COUNT(session_id) FROM $this->session_db_name WHERE session_id = '$id'");
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
	
	$sess_data	= $this->db->escape($sess_data);
	$curent_session = $this->session_exist($id);
	$expiration	= $this->upcoming_time($this->sesion_expire);
	
	if( $curent_session == 0 ){
	    
	    $sql = "INSERT INTO $this->session_db_name (session_id, session_data, session_expiration) VALUES('$id', '$sess_data', '$expiration')";
	}
	else {
	    
	    $sql  = "UPDATE $this->session_db_name SET session_data ='$sess_data', session_expiration = '$expiration' WHERE session_id ='$id'";
	}
	
	return $this->db->query($sql);
    }
    
    /**
     * EN: Remove session data
     *
     * @param string
     * @return boolean
     */
    public function session_destroy($id){
	
	return $this->db->delete($this->session_db_name, array('session_id' => $id));
    }
    
    /**
     * EN: Clean all expired record in db trigered by PHP Session Garbage Collection
     *
     * @param date I don't think we still need this parameter since the expired date was store in db.
     * @return boolean
     */
    public function session_gc($maxlifetime = ''){
	
	return $this->db->query( "DELETE FROM $this->session_db_name WHERE session_expiration < ".time().")" );
    }
}