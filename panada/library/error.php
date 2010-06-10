<?php defined('THISPATH') or die('Tidak diperkenankan mengakses file secara langsung.');
/**
 * Panada error template.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Kandar
 * @since	Version 0.1
 */

class Library_error {
    
    public static function instance(){
	return new Library_error();
    }
    
    private function header($code){
        
        $return = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd" >
                    <html lang="en"><head><title>Error: '.$code.'</title></head>
                    <body>';
       
        printf($return);
    }
    
    private function footer(){
        
        printf('</body></html>');
    }
    
    
    function _404(){
        
        self::set_status_header(404);
        self::header(404);
        echo '<h1>Error: Page not Found!</h1>';
        self::footer();
        exit;
    }
    
    function _400(){
        
        self::set_status_header(400);
        self::header(400);
        echo '<h1>Error: Bad Request!</h1>';
        self::footer();
        exit;
    }
    
    function _500($title = '500', $message = '<h1>Error: Bad Request!</h1>'){
	
	self::set_status_header(500);
        self::header($title);
        echo $message;
        self::footer();
        exit;
    }
    
    function database($message = ''){
	
	$title = 'Database';
	$body = '<h2>Error: Database</h2>';
	$body .= '<p>'.$message.'</p>';
	
	self::_500($title, $body);
    }
    
    function costume($code = 200, $message = ''){
	
	self::set_status_header($code);
        self::header($code);
        echo $message;
        self::footer();
        
    }
    
    function set_status_header($code = 200, $text = ''){
        
	$status = array(
                200	=> 'OK',
                201	=> 'Created',
                202	=> 'Accepted',
                203	=> 'Non-Authoritative Information',
                204	=> 'No Content',
                205	=> 'Reset Content',
                206	=> 'Partial Content',
                300	=> 'Multiple Choices',
                301	=> 'Moved Permanently',
                302	=> 'Found',
                304	=> 'Not Modified',
                305	=> 'Use Proxy',
                307	=> 'Temporary Redirect',
                400	=> 'Bad Request',
                401	=> 'Unauthorized',
                403	=> 'Forbidden',
                404	=> 'Not Found',
                405	=> 'Method Not Allowed',
                406	=> 'Not Acceptable',
                407	=> 'Proxy Authentication Required',
                408	=> 'Request Timeout',
                409	=> 'Conflict',
                410	=> 'Gone',
                411	=> 'Length Required',
                412	=> 'Precondition Failed',
                413	=> 'Request Entity Too Large',
                414	=> 'Request-URI Too Long',
                415	=> 'Unsupported Media Type',
                416	=> 'Requested Range Not Satisfiable',
                417	=> 'Expectation Failed',
                500	=> 'Internal Server Error',
                501	=> 'Not Implemented',
                502	=> 'Bad Gateway',
                503	=> 'Service Unavailable',
                504	=> 'Gateway Timeout',
                505	=> 'HTTP Version Not Supported'
            );

        if ($code == '' OR ! is_numeric($code))
            die('Status codes must be numeric');
    
        if (isset($status[$code]) AND $text == '')		
            $text = $status[$code];
            
        if ($text == '')
            die('No status text available. Please check your status code number or supply your own message text.');
            
        $server_protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : FALSE;
    
        if (substr(php_sapi_name(), 0, 3) == 'cgi')
            header("Status: $code $text", TRUE);
        elseif ($server_protocol == 'HTTP/1.1' OR $server_protocol == 'HTTP/1.0')
            header($server_protocol." $code $text", TRUE, $code);
        else
            header("HTTP/1.1 $code $text", TRUE, $code);
    }
    
}// End Library_error