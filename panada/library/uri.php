<?php defined('THISPATH') or die('Tidak diperkenankan mengakses file secara langsung.');
/**
 * Panada URL Parsesr.
 * 
 * @package	Panada
 * @subpackage	Library
 * @author	Kandar.
 * @since	Version 0.1
 */

class Library_uri {
    
    /**
     * Extract the url into string query.
     * 
     * @return  string
     */
    function extract_uri_string(){
	
	// ID: Pertama, coba dapatkan url menggunakan variable golbal $_SERVER['PATH_INFO'].
        $path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
        if (trim($path, '/') != '' && $path != '/index.php')
            return $path;
	
    
	// ID: coba dengan $_SERVER['QUERY_STRING'].
	$path =  (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
        if (trim($path, '/') != '')
            return $path;
	
	
	// ID: Masih belum berhasil juga?? coba lagi dengan $_SERVER['ORIG_PATH_INFO']
        $path = str_replace($_SERVER['SCRIPT_NAME'], '', (isset($_SERVER['ORIG_PATH_INFO'])) ? $_SERVER['ORIG_PATH_INFO'] : @getenv('ORIG_PATH_INFO'));
        if (trim($path, '/') != '' && $path != '/index.php')
            return $path;
        
	
	// ID: Jika tidak berhasil, deklarasikan array berisikan komponen2 yang tidak digunakan. Array ini akan digunakan di dalam fungis str_replace di bawah.
	$script_remove_string = explode('/', $_SERVER['SCRIPT_NAME']);
	
	// ID: Jika cara pertama tidak berhasil, coba lagi menggunakan varibel global $_SERVER["PHP_SELF"]
	$path = trim(str_replace($script_remove_string, '', $_SERVER["PHP_SELF"]), '/');
	if( $path != '' )
	    return '/'.$path;
	
	
	// ID: Belum berhasil juga? coba dengan $_SERVER["REQUEST_URI"]
	$path = trim(str_replace($script_remove_string, '', $_SERVER["REQUEST_URI"]), '/');
	$path = $this->remove_query($path);
	if (trim($path, '/') != '')
	    return '/'.$path;
	
	
	// ID: Terakhir, coba gunakan parameter base_url dari file config.php. Ini untuk menangani masalah yang biasa muncul di Nginx. 
	$path = str_replace($GLOBALS['CONFIG']['base_url'], '', ($this->is_https())?'https://':'http://' . $_SERVER['SERVER_NAME']. $_SERVER['REQUEST_URI']);
	$path = $this->remove_query($path);
	if (trim($path, '/') != '' && trim($path, '/') != 'index.php')
	    return '/'.$path;
	
        return false;
    }
    
    /**
     * EN: Does this site use https?
     *
     * @return boolean
     */
    function is_https() {
	
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
     * ID: Jika di url ada query seperti ini ?abc=123&def=345, string-nya akan terbawa, untuk itu harus dibersihkan terlebih dahulu.
     *
     * @param string
     * @return string
     */
    function remove_query($path){
	
	$path_ar = explode('?', $path);
	if(count($path_ar) > 0)
	    $path = $path_ar[0];
	
	return $path;
    }
    
    /**
     * EN: Break the string given from extract_uri_string() into class, method and request.
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
     * EN: Fatch class name from the url.
     *
     * @return  string
     */
    function fetch_class(){
	
	if( $uri_string = $this->break_uri_string(1) ){
	    
	    if( $this->strip_uri_string($uri_string) )
		return strtolower($uri_string);
	    else
		return false;
	}
	else {
	    
	    return 'welcome';
	}
    }
    
    /**
     * EN: Fatch method name from the url.
     *
     * @return  string
     */
    function fetch_method(){
	
	$uri_string = $this->break_uri_string(2);
	
	if( isset($uri_string) && ! empty($uri_string) ){
	    
	    if( $this->strip_uri_string($uri_string) )
		return strtolower($uri_string);
	    else
		return '';
	    
	}
	else {
	    
	    return 'index';
	}
    }
    
    /**
     * EN: Fatch GET request from the url.
     *
     * @param	int
     * @return  array
     */
    function fetch_request($segment = 3){
	
	$uri_string = $this->break_uri_string($segment);
	
	if( isset($uri_string) && ! empty($uri_string) )
	    return array_slice($this->break_uri_string(), $segment);
	else
	    return false;
    }
    
    /**
     * EN: Cleaner for class and method name
     *
     * @param string
     * @return boolean
     */
    function strip_uri_string($uri){
	
	$uri = ( !preg_match('/[^a-zA-Z0-9_.-]/', $uri) ) ? true : false;
	return $uri;
    }
    
} //End Library_uri