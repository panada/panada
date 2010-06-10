<?php defined('THISPATH') or die('Tidak diperkenankan mengakses file secara langsung.');
/**
 * Panada URL Parsesr.
 * 
 * @package	Panada
 * @subpackage	Library
 * @author	Kandar. Modified from CodeIgniter URI Class version: 1.7.2. {@link http://codeigniter.com}
 * @since	Version 0.1
 */

class Library_uri {
    
    /**
     * Extract the url into string query.
     * 
     * @return  string
     */
    function extract_uri_string(){
	
        $path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
        if (trim($path, '/') != '' && $path != '/index.php'){
            return $path;
        }
        
        $path =  (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
        if (trim($path, '/') != ''){
            return $path;
        }
        
        $path = str_replace($_SERVER['SCRIPT_NAME'], '', (isset($_SERVER['ORIG_PATH_INFO'])) ? $_SERVER['ORIG_PATH_INFO'] : @getenv('ORIG_PATH_INFO'));
        if (trim($path, '/') != '' && $path != '/index.php'){
            return $path;
        }
        
        return false;
    }
    
    /**
     * Break the string given from extract_uri_string() into
     * class, method and request.
     *
     * @param	integer
     * @return  string
     */
    function break_uri_string($segment = 0){
    
	$uri_string = $this->extract_uri_string();
	$uri_string = explode('/', $uri_string);
	
	if( $segment > 0 )
	    return isset( $uri_string[$segment] )? $uri_string[$segment]:false;
	else
	    return $uri_string;
    }
    
    /**
     * Fath class name from the url.
     *
     * @return  string
     */
    function fetch_class(){
	
	if( $uri_string = $this->break_uri_string(1) ){
	    
	    if( $this->strip_uri_string($uri_string) ){
		
		return strtolower($uri_string);
	    }
	    else {
		
		return false;
	    }
	}
	else {
	    
	    return 'welcome';
	}
    }
    
    /**
     * Fath method name from the url.
     *
     * @return  string
     */
    function fetch_method(){
	
	$uri_string = $this->break_uri_string(2);
	
	if( isset($uri_string) && ! empty($uri_string) ){
	    
	    if( $this->strip_uri_string($uri_string) ){
		
		return strtolower($uri_string);
	    }
	    else {
		
		return '';
	    }
	}
	else {
	    
	    return 'index';
	}
    }
    
    /**
     * Fath GET request from the url.
     *
     * @return  array
     */
    function fetch_request(){
	
	$uri_string = $this->break_uri_string(3);
	
	if( isset($uri_string) && ! empty($uri_string) ){
	    
	    return array_slice($this->break_uri_string(), 3);
	}
	else {
	    
	    return false;
	}
    }
    
    /**
     * Cleaner for class and method name
     *
     * @return boolean
     */
    function strip_uri_string($uri){
	
	$uri = ( !preg_match('/[^a-zA-Z0-9_.-]/', $uri) ) ? true : false;
	return $uri;
    }
    
} //End Library_uri