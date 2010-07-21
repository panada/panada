<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada session hendler.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */

class Library_session {
    
    /**
    * @var integer	EN: This variable set the maximum life in seconds of a session file on the server since last activity.
    *			ID: Parameter ini menentukan berapa lama file session disimpan di server sejak aktivitas terakhir.
    */
    public $sesion_expire = 7200; //2 hour
    
    /**
     * @var string	EN: Change the default PHP session name (PHPSESSIONID) to Panada session name (PAN_SID).
     * 			ID: Merubah nama cookie session PHP dari PHPSESSIONID menjadi PAN_SID.
     */
    public $session_name = 'PAN_SID';
    
    /**
     * @var integer	EN: Sets the session cookies to N seconds.
     *			ID: Menentukan berapa lama cookie session disimpan di browser.
     */
    public $session_cookie_expire = 0;
    
    /**
     * @var string	EN: This session id.
     */
    public $session_id;
    
    /**
     * @var string	EN: Session cookie path.
     * 			ID: Lokasi path di mana cookie berlaku.
     */
    public $session_cookie_path = '/';
    
    /**
     * @var boolean	EN: Define the cookie only working on https or not.
     *			ID: Menentukan apakah cookie hanya berlaku pada https atau tidak.
     */
    public $session_cookie_secure = false;
    
    /**
     * @var string	EN: Define the cookie domain.
     *			ID: Menentukan domain cookie.
     */
    public $session_cookie_domain = '';
    
    /**
     * @var string	EN: Where we store the session? file (PHP native) or db.
     *			ID: Di mana kita akan simpan session? file (PHP native) atau db.
     */
    public $session_store = 'native';
    
    /**
     * @var string	EN: Session table name.
     *			ID: Nama table session.
     */
    public $session_db_name = 'sessions';
    
    /**
     * Class constructor.
     *
     * EN: Set costumized PHP Session parameter.
     * ID: Modifikasi konfigurasi PHP session.
     *
     * @return void
     */
    
    public function __construct(){
	
	$this->sesion_expire		= $GLOBALS['CONFIG']['session']['session_expire'];
	$this->session_name		= $GLOBALS['CONFIG']['session']['session_name'];
	$this->session_cookie_expire	= $GLOBALS['CONFIG']['session']['session_cookie_expire'];
	$this->session_cookie_path	= $GLOBALS['CONFIG']['session']['session_cookie_path'];
	$this->session_cookie_secure	= $GLOBALS['CONFIG']['session']['session_cookie_secure'];
	$this->session_cookie_domain	= $GLOBALS['CONFIG']['session']['session_cookie_domain'];
	$this->session_store		= $GLOBALS['CONFIG']['session']['session_store'];
	$this->session_db_name		= $GLOBALS['CONFIG']['session']['session_db_name'];
	
	ini_set('session.gc_maxlifetime', $this->sesion_expire);
	
	session_set_cookie_params(
	    $this->session_cookie_expire,
	    $this->session_cookie_path,
	    $this->session_cookie_domain,
	    $this->session_cookie_secure
	);
	
        session_name($this->session_name);
	
	if( $this->session_store == 'db' ){
	    
	    /**
	     * EN: Does the db object exist??
	     * ID: Memastikan apakah object db sudah dideklarasikan sebelumnya atau belum.
	     */
	    if( ! isset($this->db) ){
		$this->db = new library_db();
	    }
	    
	    $this->initial_save_session();
	}
        
	/* EN: Lets start the session. */
	$this->init();
        
    }
    
    /**
     * EN: Initiate the PHP native session
     *
     * @return void
     */
    private function init(){
	
	if (session_id() == ''){
	    
	    /**
	     * ID: Pada OS Debian/Ubuntu saat melakukan proses GC (garbage collection)
	     * Akan muncul error "failed: Permission denied". Hal ini karena secara default
	     * Debian hanya mengijinkan root untuk memodifikasi file session yang ada di
	     * dalam folder /var/lib/php5.
	     */
	    
	    @session_start();
	    $this->session_id = session_id();
	}
    }
    
    /**
     * EN: Get next time in minute. Default is 300 sec or five minute.
     * ID: Dapatkan waktu beberapa menit kemudian. Default lima menit.
     *
     * @return int
     */
    private function upcomming_time($s = 300){
	
	return mktime(date('H'), date('i'), date('s') + $s, date('n'), date('j'), date('Y'));
    }
    
    /**
     * EN: Remove existing session file/record and cookie then replace it with new one but still with old values.
     *     However, automatic session regeneration isn't recommended because it can cause a race condition when
     *     you have multiple session requests while regenerating the session id (most commonly noticed with ajax
     *     requests). For security reasons it's recommended that you manually call regenerate() whenever a visitor's
     *     session privileges are escalated (e.g. they logged in, accessed a restricted area, etc).
     *     
     * ID: Hapus file/record session yang ada dan ganti dengan yang baru dengan tetep menggunakan nilai yang sama.
     *
     * @return void
     */
    public function regenerate(){
	
	session_regenerate_id(true);
	$this->session_id = session_id();
    }
    
    /**
     * EN: Save new session
     *
     * @param string|array
     * @param string|array|object
     * @return void
     */
    public function set($name, $value = ''){
        
	if( is_array($name) ) {
	    foreach($name AS $key => $val)
		$_SESSION[$key] = $val;
	}
	else {
	    $_SESSION[$name] = $value;
	}
    }

    /**
     * EN: Get the session vale.
     *
     * @param string
     * @return string|array|object
     */
    public function get($name){
        
	if(isset($_SESSION[$name]))
	    return $_SESSION[$name];
	else
	    return false;
    }
    
    /**
     * EN: Remove/unset the session value.
     *
     * @param string
     * @return void
     */
    public function remove($name){
        
	unset($_SESSION[$name]);
    }
    
    /**
     * EN: Complitly remove the file session at the server and the cookie file in user's browser.
     *
     * @return void
     */
    public function destroy(){
	
	$params = session_get_cookie_params();
	
	setcookie($this->session_name, '', time() - 42000,
	    $params['path'], $params['domain'],
	    $params['secure'], $params['httponly']
	);
	
	session_unset();
	session_destroy();
    }
    
    /**
     * EN: Tell to browser to not cache the page.
     *
     * @return void
     */
    public function session_clear_all(){
	
	header('Expires: Mon, 1 Jul 1998 01:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Last-Modified: ' . gmdate( 'D, j M Y H:i:s' ) . ' GMT' );
        
        $this->destroy();
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
		WHERE session_id ='$id' AND session_expiration > UNIX_TIMESTAMP(NOW())";
	
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
	
	$session = $this->db->get_var("SELECT COUNT(session_id) FROM $this->session_db_name WHERE session_id = '$id'");
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
	$expiration	= $this->upcomming_time($this->sesion_expire);
	
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
	
	return $this->db->query( "DELETE FROM $this->session_db_name WHERE session_expiration < UNIX_TIMESTAMP(NOW())" );
    }
    
    /**
     * EN: Initiate session save handler
     * 
     * @return void
     */
    private function initial_save_session(){
	
	session_set_save_handler (
	    array(&$this, 'session_start'),
	    array(&$this, 'session_end'),
	    array(&$this, 'session_read'),
	    array(&$this, 'session_write'),
	    array(&$this, 'session_destroy'),
	    array(&$this, 'session_gc')
	);
    }
    
}// End Library_session