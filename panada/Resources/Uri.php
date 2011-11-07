<?php
/**
 * Hendler for request and mapped into, controller, metohd and requests.
 *
 * @author Iskandar Soesman <k4ndar@yahoo.com>
 * @link http://panadaframework.com/
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @since version 0.1.0
 * @package Resources
 */
namespace Resources;

final class Uri {
    
    private $pathUri = array();
    public $baseUri, $indexFile = null;
    
    /**
     * Class constructor
     *
     * Difine the SPI mode, cli or web/http
     *
     * @return void
     */
    public function __construct(){
	
	if(PHP_SAPI == 'cli'){
	    $this->pathUri = array_slice($_SERVER['argv'], 1);
	    return;
	}
	
	if( array_search(INDEX_FILE, explode('/', $_SERVER['REQUEST_URI'])) !== false )
	    $this->indexFile = INDEX_FILE . '/';
	
	$selfArray      = explode('/', $_SERVER['PHP_SELF']);
	$selfKey        = array_search(INDEX_FILE, $selfArray);
	$this->pathUri  = array_slice($selfArray, ($selfKey + 1));
	$this->baseUri  = ( $this->isHttps() ) ? 'https://':'http://'. $_SERVER['HTTP_HOST'].implode('/', array_slice($selfArray, 0, $selfKey)) .'/';  
	
    }

    /**
     * Does this site use https?
     *
     * @return boolean
     */
    public function isHttps() {
	
	if ( isset($_SERVER['HTTPS']) ) {
	    
	    if ( 'on' == strtolower($_SERVER['HTTPS']) )
		return true;
	    if ( '1' == $_SERVER['HTTPS'] )
		return true;
	}
	elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
	    
	    return true;
	}

	return false;
    }

    /**
     * Clean the 'standard' model query.
     *
     * @param string
     * @return string
     */
    public function removeQuery($path){
	
	$pathAr = explode('?', $path);
	if(count($pathAr) > 0)
	    $path = $pathAr[0];
	
	return $path;
    }

    /**
     * Break the string given from extractUriString() into class, method and request.
     *
     * @param    integer
     * @return  string
     */
    public function path($segment = false){
	
	if( $segment !== false )
	    return isset( $this->pathUri[$segment] )? $this->pathUri[$segment]:false;
	else
	    return $this->pathUri;
    }

    /**
     * Get class name from the url.
     *
     * @return  string
     */
    public function getClass(){
	
	if( $uriString = $this->path(0) ){
	    
	    if( $this->stripUriString($uriString) )
	    return $uriString;
	    else
	    return false;
	}
	else {
	    
	    return 'home';
	}
    }

    /**
     * Get method name from the url.
     *
     * @return  string
     */
    public function getMethod($default = 'index'){
	
	$uriString = $this->path(1);

	if( isset($uriString) && ! empty($uriString) ){

	    if( $this->stripUriString($uriString) )
		return $uriString;
	    else
		return '';
    
	    }
	    else {
    
	    return $default;
	}
    }

    /**
     * Get "GET" request from the url.
     *
     * @param    int
     * @return  array
     */
    public function getRequests($segment = 2){

	$uriString = $this->path($segment);
    
	if( isset($uriString) && ! empty($uriString) ) {
    
	    $requests = array_slice($this->path(), $segment);
    
	    return $requests;
	}
	else {
	    return false;
	}
    }

    /**
     * Cleaner for class and method name
     *
     * @param string
     * @return boolean
     */
    public function stripUriString($uri){

	$uri = ( ! preg_match('/[^a-zA-Z0-9_.-]/', $uri) ) ? true : false;
	return $uri;
    }
}