<?php defined('THISPATH') or die('Tidak diperkenankan mengakses file secara langsung.');
/**
 * Panada session hendler.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Kandar
 * @since	Version 0.1
 */

class Library_session {
    
    /**
    * EN: This variable sets the maximum life in seconds of a session file on the server.
    * ID: Parameter ini menentukan berapa lama file session disimpan di server.
    */
    var $sesion_expire = 86400; //24 hour
    
    /**
     * EN: Change the default php session name (PHPSESSIONID) to Panada session name (PAN_SID).
     * ID: Merubah nama cookie session PHP dari PHPSESSIONID menjadi PAN_SID.
     */
    var $session_name = 'PAN_SID';
    
    /**
     * EN: Sets the session cookies to N seconds.
     * ID: Menentukan berapa lama cookie session disimpan di browser.
     */
    var $session_cookie_expire = 0;
    
    /**
     * EN: Session cookie path
     * ID: Lokasi path di mana cookie berlaku.
     */
    var $session_cookie_path = '/';
    
    /**
     * EN: Define the cookie only working on https or not.
     * ID: Menentukan apakah cookie hanya berlaku pada https atau tidak.
     */
    var $session_cooke_secure = false;
    
    /**
     * EN: Define the cookie domain.
     * ID: Menentukan domain cookie.
     */
    var $session_cookie_domain = '';
    
    var $session_in_db = false;
    
    var $session_db_name = 'sessions';
    
    /**
     * Class constructor.
     *
     * EN: Set costumized PHP Session parameter.
     * ID: Modifikasi konfigurasi PHP session.
     *
     * @return void
     */
    
    function __construct(){
	
	$this->sesion_expire		= $GLOBALS['CONFIG']['session']['sesion_expire'];
	$this->session_name		= $GLOBALS['CONFIG']['session']['session_name'];
	$this->session_cookie_expire	= $GLOBALS['CONFIG']['session']['session_cookie_expire'];
	$this->session_cookie_path	= $GLOBALS['CONFIG']['session']['session_cookie_path'];
	$this->session_cooke_secure	= $GLOBALS['CONFIG']['session']['session_cooke_secure'];
	$this->session_cookie_domain	= $GLOBALS['CONFIG']['session']['session_cookie_domain'];
	$this->session_in_db		= $GLOBALS['CONFIG']['session']['session_in_db'];
	$this->session_db_name		= $GLOBALS['CONFIG']['session']['session_db_name'];
	
	ini_set('session.gc_maxlifetime', $this->sesion_expire);
	
	session_set_cookie_params(
				  $this->session_cookie_expire,
				  $this->session_cookie_path,
				  $this->session_cookie_domain,
				  $this->session_cooke_secure
				  );
	
        session_name($this->session_name);
	
	if($this->session_in_db){
	    $this->initial_save_session();
	}
        
	/* Lets start the session. */
	$this->init();
        
    }
    
    /**
     * EN: Initiate the PHP native session
     *
     * @return void
     */
    function init(){
	
	if (session_id() == ''){
	    
	    /**
	     * ID: Pada OS Debian/Ubuntu saat melakukan proses GC (garbage collection)
	     * Akan muncul error "failed: Permission denied". Hal ini karena secara default
	     * Debian hanya mengijinkan root untuk memodifikasi file session yang ada di
	     * dalam folder /var/lib/php5.
	     */
	    @session_start();
	    
	    if( ! isset($_SESSION['pan_regenerate_time']) ){
		$_SESSION['pan_regenerate_time'] = $this->upcomming_time();
	    }
	}
	else {
	    
	    if( $_SESSION['pan_regenerate_time'] < time() ){
		
		$this->regenerate();
	    }
	}
    }
    
    /**
     * EN: Get next five minute time
     * ID: Dapatkan waktu untuk lima menit kedepan.
     *
     * @return int
     */
    function upcomming_time(){
	
	return mktime(date('H'), date('i') + 5, date('s'), date('m'), date('d'), date('Y'));
    }
    
    /**
     * EN: Remove existing session file/record and cookie then replace it with new one but still with old values.
     * ID: Hapus file/record session yang ada dan ganti dengan yang baru dengan tetep menggunakan nila yang sama.
     *
     * @return void
     */
    function regenerate(){
	
	$_SESSION['pan_regenerate_time'] = $this->upcomming_time();
	session_regenerate_id(true);
    }
    
    /**
     * EN: Save new session
     *
     * @param string
     * @param string|array|object
     * @return string|array|object
     */
    function set($name, $value){
        
	$_SESSION[$name] = $value;
    }

    /**
     * EN: Get the session vale.
     *
     * @param string
     * @return string|array|object
     */
    function get($name){
        
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
    function remove($name){
        
	unset($_SESSION[$name]);
    }
    
    /**
     * EN: Complitly remove the file session at the server and the cookie file on user browser.
     *
     * @return void
     */
    function destroy(){
	
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
    function session_clear_all(){
	
	header('Expires: Mon, 1 Jul 1998 01:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Last-Modified: ' . gmdate( 'D, j M Y H:i:s' ) . ' GMT' );
        
        $this->destroy();
    }
    
    function session_start($save_path, $session_name){
	
	//error_log('Session save path: '.$save_path.' and name: '.$session_name);
    }
    
    function session_end(){
	
    }
    
    function session_read($id){
	
	$sess_file = $this->session_save_path.'/sess_'.$id;
	return (string) @file_get_contents($sess_file);
    }
    
    function session_write($id, $sess_data){
	
	$sess_file = $this->session_save_path.'/sess_'.$id;
	
	if( $fp = @fopen($sess_file, 'w') ){
	    $return = fwrite($fp, $sess_data);
	    fclose($fp);
	    
	    return $return;
	}
	else {
	    
	    return false;
	}
    }
    
    function session_destroy($id){
	
	$sess_file = $this->session_save_path.'/sess_'.$id;
	@unlink($sess_file);
    }
    
    function session_gc($maxlifetime){
	
	error_log('Session maxlifetime: '.$maxlifetime);
	
	foreach (glob($this->session_save_path.'/sess_*') as $filename){
	    if (filemtime($filename) + $maxlifetime < time()) {
		@unlink($filename);
	    }
	}
	
	return true;
    }
    
    function initial_save_session(){
	
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