<?php
/**
 * Cookies base session.
 *
 * @package	Driver
 * @subpackage	Session
 * @author	Iskandar Soesman
 * @since	Version 0.3
 */
namespace Drivers\Session;

class Cookie {
    
    public $sessionName = 'PAN_SID';
    public $sessionCookieExpire = 0;
    public $sessionCookiePath = '/';
    public $sessionCookieSecure = false;
    public $sessionCookieDomain = '';
    public $cookieChekSumName = 'chs';
    protected $hashKey = 'my_key';
    protected $curentValues = array();
    
    /**
     * Define all properties needed.
     *
     * @param object $config_instance
     * @return void
     */
    public function __construct( $config ){
	
        $this->sessionName          = $config['name'];
        $this->sessionCookieExpire  = $config['expiration'];
        $this->sessionCookiePath    = $config['cookiePath'];
        $this->sessionCookieSecure  = $config['cookieSecure'];
        $this->sessionCookieDomain  = $config['cookieDomain'];
	$this->hashKey		    = $config['secretKey'];
        
	/**
	 * If set, we have to make sure this value is valid.
	 * If true, then update the expiration date. Otherwise, destroy it!
	 */
        if( isset( $_COOKIE[$this->sessionName] ) ){
            
            parse_str( $_COOKIE[$this->sessionName], $currentValues);
            $this->curentValues = $currentValues;
            
            if( ! $this->validatesCookieValues() )
                $this->destroy();
	    else
		$this->setSessionValues();
        }
        else{
            
            $this->curentValues['_d'] = '.';
            $this->setSessionValues();
        }
        
    }
    
    /**
     * Create a second cookie that content the md5sum of the values.
     * Every new value will update this checksum too.
     * 
     * @return void
     */
    protected function setCheckSum(){
        
        $curentValues = $this->curentValues;
        
        $curentValues['agent'] = $_SERVER['HTTP_USER_AGENT'];
        
        $values = md5(http_build_query($curentValues).$this->hashKey);
        
        $this->setCookie($this->cookieChekSumName, $values);
    }
    
    /**
     * Validating cookie value against the md5sum.
     *
     * @return bool
     */
    private function validatesCookieValues(){
        
        $curentValues = $this->curentValues;
        
        $curentValues['agent'] = $_SERVER['HTTP_USER_AGENT'];
        
        $values = md5(http_build_query($curentValues).$this->hashKey);
	
	if( ! isset($_COOKIE[$this->cookieChekSumName]) )
	    return false;
        
        if( $values != $_COOKIE[$this->cookieChekSumName] )
            return false;
        
        return true;
    }
    
    /**
     * Build and construct the cookie values.
     *
     * @return void
     */
    private function setSessionValues(){
        
        $value = http_build_query($this->curentValues);

        $this->setCookie($this->sessionName, $value);
        
        $this->setCheckSum();
    }
    
    /**
     * Create a cookie
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    private function setCookie($name, $value = ''){
        
        setcookie(
            $name,
            $value,
            time() + $this->sessionCookieExpire,
            $this->sessionCookiePath,
            $this->sessionCookieDomain,
            $this->sessionCookieSecure
        );
    }
    
    /**
     * Set a new session value.
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function setValue($name, $value = ''){
        
	if( is_array($name) ) {
	    foreach($name AS $key => $val)
		$this->curentValues[$key] = $val;
	}
	else {
	    $this->curentValues[$name] = $value;
	}
        
        $this->setSessionValues();
    }
    
    /**
     * Get a session value base on the name.
     *
     * @param string $name
     * @return mix
     */
    public function getValue( $name = null ){
        
        $curentValues = $this->curentValues;
        unset($curentValues['_d']);
        
        if( empty($curentValues) )
            return false;
        
        if( is_null($name) )
            return $curentValues;
        
	if( isset($curentValues[$name]) )
	    return $curentValues[$name];
	
	return false;
    }
    
    /**
     * Remove certain session value.
     *
     * @param string $name
     * @return void
     */
    public function deleteValue($name){
        
        unset( $this->curentValues[$name] );
        $this->setSessionValues();
    }
    
    /**
     * Clear all session value
     *
     * @return void
     */
    public function destroy(){
        
        $this->curentValues = array();
        $this->curentValues['_d'] = '.';
        
        $this->setSessionValues();
    }
    
    /**
     * Clear the values and remove the cookie.
     *
     * @return void
     */
    public function clearAll(){
        
        header('Expires: Mon, 1 Jul 1998 01:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Last-Modified: ' . gmdate( 'D, j M Y H:i:s' ) . ' GMT' );
        
        $this->curentValues = array();
        
        $this->sessionCookieExpire = strtotime('-10 years');
        $this->setCookie($this->sessionName);
        $this->setCookie($this->cookieChekSumName);
        
        $this->setSessionValues();
    }
    
    /**
     * Regenerate the cookie id
     */
    public function regenerateId(){
        return;
    }   
}