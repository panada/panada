<?php
/**
 * Cookies base session.
 *
 * @package	Driver
 * @subpackage	Session
 * @author	Iskandar Soesman
 * @since	Version 0.3
 */
namespace Dirvers\Session;

class Cookie {
    
    public $session_name = 'PAN_SID';
    public $session_cookie_expire = 0;
    public $session_cookie_path = '/';
    public $session_cookie_secure = false;
    public $session_cookie_domain = '';
    public $cookie_chek_sum_name = 'chs';
    protected $hash_key = 'my_key';
    protected $curent_values = array();
    
    /**
     * Define all properties needed.
     *
     * @param object $config_instance
     * @return void
     */
    public function __construct( $config_instance ){
        
        $this->session_name             = $config_instance->name;
        $this->session_cookie_expire    = $config_instance->expiration;
        $this->session_cookie_path      = $config_instance->cookie_path;
        $this->session_cookie_secure    = $config_instance->cookie_secure;
        $this->session_cookie_domain    = $config_instance->cookie_domain;
	$this->hash_key			= $config_instance->secret_key;
        
	/**
	 * If set, we have to make sure this value is valid.
	 * If true, then update the expiration date. Otherwise, destroy it!
	 */
        if( isset( $_COOKIE[$this->session_name] ) ){
            
            parse_str( $_COOKIE[$this->session_name], $current_values);
            $this->curent_values = $current_values;
            
            if( ! $this->validates_cookie_values() )
                $this->destroy();
	    else
		$this->set_session_values();
        }
        else{
            
            $this->curent_values['_d'] = '.';
            $this->set_session_values();
        }
        
    }
    
    /**
     * Create a second cooke that content the md5sum of the values.
     * Every new value will update this checksum too.
     * 
     * @return void
     */
    protected function set_check_sum(){
        
        $curent_values = $this->curent_values;
        
        $curent_values['agent'] = $_SERVER['HTTP_USER_AGENT'];
        
        $values = md5(http_build_query($curent_values).$this->hash_key);
        
        $this->set_cookie($this->cookie_chek_sum_name, $values);
    }
    
    /**
     * Validating cookie value against the md5sum.
     *
     * @return bool
     */
    public function validates_cookie_values(){
        
        $curent_values = $this->curent_values;
        
        $curent_values['agent'] = $_SERVER['HTTP_USER_AGENT'];
        
        $values = md5(http_build_query($curent_values).$this->hash_key);
        
        if( $values != $_COOKIE[$this->cookie_chek_sum_name] )
            return false;
        
        return true;
    }
    
    /**
     * Build and construct the cooke values.
     *
     * @return void
     */
    protected function set_session_values(){
        
        $value = http_build_query($this->curent_values);

        $this->set_cookie($this->session_name, $value);
        
        $this->set_check_sum();
    }
    
    /**
     * Create a cooke
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    protected function set_cookie($name, $value = ''){
        
        setcookie(
            $name,
            $value,
            time() + $this->session_cookie_expire,
            $this->session_cookie_path,
            $this->session_cookie_domain,
            $this->session_cookie_secure
        );
    }
    
    /**
     * Set a new session value.
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function set($name, $value = ''){
        
	if( is_array($name) ) {
	    foreach($name AS $key => $val)
		$this->curent_values[$key] = $val;
	}
	else {
	    $this->curent_values[$name] = $value;
	}
        
        $this->set_session_values();
    }
    
    /**
     * Get a session value base on the name.
     *
     * @param string $name
     * @return mix
     */
    public function get( $name = null ){
        
        $curent_values = $this->curent_values;
        unset($curent_values['_d']);
        
        if( empty($curent_values) )
            return false;
        
        if( is_null($name) )
            return $curent_values;
        
	if( isset($curent_values[$name]) )
	    return $curent_values[$name];
	
	return false;
    }
    
    /**
     * Remove certain session value.
     *
     * @param string $name
     * @return void
     */
    public function remove($name){
        
        unset( $this->curent_values[$name] );
        $this->set_session_values();
    }
    
    /**
     * Clear all session value
     *
     * @return void
     */
    public function destroy(){
        
        $this->curent_values = array();
        $this->curent_values['_d'] = '.';
        
        $this->set_session_values();
    }
    
    /**
     * Clear the values and remove the cookie.
     *
     * @return void
     */
    public function session_clear_all(){
        
        header('Expires: Mon, 1 Jul 1998 01:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Last-Modified: ' . gmdate( 'D, j M Y H:i:s' ) . ' GMT' );
        
        $this->curent_values = array();
        
        $this->session_cookie_expire = strtotime('-10 years');
        $this->set_cookie($this->session_name);
        $this->set_cookie($this->cookie_chek_sum_name);
        
        $this->set_session_values();
    }
    
    /**
     * Regenerate the cooke id
     */
    public function regenerate(){
        return;
    }
    
} // End Drivers_session_cookie