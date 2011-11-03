<?php
/**
 * Panada PHP native session hendler.
 *
 * @package	Driver
 * @subpackage	Session
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */
namespace Dirvers\Session;

class Native {
    
    /**
    * @var integer	This variable set the maximum life in seconds of a session file on the server since last activity.
    */
    public $sesion_expire = 7200; //second or 2 hour
    
    /**
     * @var string	EN: Change the default PHP session name (PHPSESSIONID) to Panada session name (PAN_SID).
     */
    public $session_name = 'PAN_SID';
    
    /**
     * @var integer	EN: Sets the session cookies to N seconds.
     */
    public $session_cookie_expire = 0;
    
    /**
     * @var string	EN: This session id.
     */
    public $session_id;
    
    /**
     * @var string	EN: Session cookie path.
     */
    public $session_cookie_path = '/';
    
    /**
     * @var boolean	EN: Define the cookie only working on https or not.
     */
    public $session_cookie_secure = false;
    
    /**
     * @var string	EN: Define the cookie domain.
     */
    public $session_cookie_domain = '';
    
    /**
     * @var string	EN: Where we store the session? file (PHP native) or db.
     */
    public $session_store = 'native';
    
    /**
     * Class constructor.
     *
     * EN: Set costumized PHP Session parameter.
     *
     * @return void
     */
    
    public function __construct( $config_instance ){
	
	$this->sesion_expire		= $config_instance->expiration;
	$this->session_name		= $config_instance->name;
	$this->session_cookie_expire	= $config_instance->cookie_expire;
	$this->session_cookie_path	= $config_instance->cookie_path;
	$this->session_cookie_secure	= $config_instance->cookie_secure;
	$this->session_cookie_domain	= $config_instance->cookie_domain;
	$this->session_store		= $config_instance->driver;
	
	ini_set('session.gc_maxlifetime', $this->sesion_expire);
	
	session_set_cookie_params(
	    $this->session_cookie_expire,
	    $this->session_cookie_path,
	    $this->session_cookie_domain,
	    $this->session_cookie_secure
	);
	
        session_name($this->session_name);
	
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
     * EN: Get next time in second. Default is 300 sec or five minute.
     *
     * @return int
     */
    protected function upcoming_time($s = 300){
	
	return strtotime('+'.$s.' sec');
    }
    
    /**
     * EN: Remove existing session file/record and cookie then replace it with new one but still with old values.
     *     However, automatic session regeneration isn't recommended because it can cause a race condition when
     *     you have multiple session requests while regenerating the session id (most commonly noticed with ajax
     *     requests). For security reasons it's recommended that you manually call regenerate() whenever a visitor's
     *     session privileges are escalated (e.g. they logged in, accessed a restricted area, etc).
     *     
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
    
}// End Library_session