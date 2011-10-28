<?php
namespace Resources;

class Uri {
    
    private $pathUri = array();
    
    public function __construct(){
	
	$selfArray  	= explode('/', $_SERVER['PHP_SELF']);
	$selfKey    	= array_search(INDEX_FILE, $selfArray);
	$this->pathUri	= array_slice($selfArray, ($selfKey + 1));
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
     * EN: Clean the 'standard' model query.
     * ID: Jika di url ada query seperti ini ?abc=123&def=345, string-nya akan terbawa, untuk itu harus dibersihkan terlebih dahulu.
     *
     * @param string
     * @return string
     */
    public function removeQuery($path){
	
	$path_ar = explode('?', $path);
	if(count($path_ar) > 0)
	    $path = $path_ar[0];
	
	return $path;
    }

    /**
     * EN: Break the string given from extractUriString() into class, method and request.
     * ID: Memecah string query menjadi class, method dan request.
     *
     * @param    integer
     * @return  string
     */
    public function breakUriString($segment = false){
	
	if( $segment !== false )
	    return isset( $this->pathUri[$segment] )? $this->pathUri[$segment]:false;
	else
	    return $this->pathUri;
    }

    /**
     * EN: Get class name from the url.
     * ID: Mendapatkan nama class dari url.
     *
     * @return  string
     */
    public function getClass(){
	
	if( $uri_string = $this->breakUriString(0) ){
	    
	    if( $this->stripUriCtring($uri_string) )
	    return strtolower($uri_string);
	    else
	    return false;
	}
	else {
	    
	    return 'home';
	}
    }

    /**
     * EN: Get method name from the url.
     * ID: Mendapatkan nama method dari url.
     *
     * @return  string
     */
    public function getMethod($default = 'index'){
	
	$uri_string = $this->breakUriString(1);

	if( isset($uri_string) && ! empty($uri_string) ){

	    if( $this->stripUriCtring($uri_string) )
		return strtolower($uri_string);
	    else
		return '';
    
	    }
	    else {
    
	    return $default;
	}
    }

    /**
     * EN: Get "GET" request from the url.
     * ID: Mendapatkan request dari url.
     *
     * @param    int
     * @return  array
     */
    public function getRequests($segment = 2){

	$uri_string = $this->breakUriString($segment);
    
	if( isset($uri_string) && ! empty($uri_string) ) {
    
	    $requests = array_slice($this->breakUriString(), $segment);
    
	    return $requests;
	}
	else {
	    return false;
	}
    }

    /**
     * EN: Cleaner for class and method name
     * ID: Membersihkan nama clas dan method dari karakter yang tidak perlu.
     *
     * @param string
     * @return boolean
     */
    public function stripUriCtring($uri){

	$uri = ( ! preg_match('/[^a-zA-Z0-9_.-]/', $uri) ) ? true : false;
	return $uri;
    }

}