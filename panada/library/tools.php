<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada Tools Class.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */

class Library_tools {
    
    /**
     * EN: Set the header response status.
     * ID: Menentukan status pada header respons.
     *
     * @param int
     * @param string
     * @return string
     */
    public static function set_status_header($code = 200, $text = ''){
        
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
	
        if (isset($status[$code]) AND $text == '')		
            $text = $status[$code];
        
        $server_protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : false;
	
        if (substr(php_sapi_name(), 0, 3) == 'cgi')
            header("Status: $code $text", true);
        elseif ($server_protocol == 'HTTP/1.1' || $server_protocol == 'HTTP/1.0')
            header($server_protocol." $code $text", true, $code);
        else
            header("HTTP/1.1 $code $text", true, $code);
    }
    
    /**
     * EN: Create random string
     *
     * @param integer
     * @param boolean
     */
    public static function get_random_string($length = 12, $special_chars = true) {
        
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
	if ( $special_chars )
	    $chars .= '!@#$%^&*()';
        
	$str = '';
	for ( $i = 0; $i < $length; $i++ )
	    $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        
	return $str;
    }
    
    /**
     * EN: Thanks to djdykes {@link: http://snipplr.com/view.php?codeview&id=3491} for great array to xml class.
     * Encode an array into xml.
     *
     * @param array
     * @param string
     * @param string
     */
    public static function xml_encode($data, $root_node_name = 'data', $xml = null){
	
	// EN: Turn off compatibility mode as simple xml throws a wobbly if you don't.
	if (ini_get('zend.ze1_compatibility_mode') == 1){
	    ini_set ('zend.ze1_compatibility_mode', 0);
	}
	
	if ( is_null($xml) ){
	    $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$root_node_name />");
	}
	
	// EN: Loop through the data passed in.
	foreach($data as $key => $value){
	    // EN: No numeric keys in our xml please!
	    if (is_numeric($key)){
		// EN: Make string key...
		$key = "unknownNode_". (string) $key;
	    }
	    
	    // EN: Replace anything not alpha numeric
	    //$key = preg_replace('/[^a-z]/i', '', $key);
	    
	    // EN: If there is another array found recrusively call this function
	    if (is_array($value)){
		$node = $xml->addChild($key);
		// EN: Recrusive call.
		self::xml_encode($value, $root_node_name, $node);
	    }
	    else{
		// EN: Add single node.
		$value = htmlentities($value);
		$xml->addChild($key,$value);
	    }
		
	}
	// EN: Pass back as string. or simple xml object if you want!
	return $xml->asXML();
    }
    
    /**
     * EN: Decoded xml variable or file or url into object.
     * ID: Decode data xml menjadi object.
     *
     * @param string
     * @param string $type Flag for data type option: object | array
     */
    public static function xml_decode($xml, $type = 'object'){
	
	// Does it local file?
	if( is_file($xml) ) {
	    $xml = simplexml_load_file($xml);
	}
	// Or this is an url file ?
	elseif( filter_var($xml, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ) {
	    $xml = simplexml_load_file($xml);
	}
	// Or it just a variable.
	else {
	    $xml = new SimpleXMLElement($xml);
	}
	
	switch ($type) {
	    case 'array':
		return (array) self::object_to_array($xml);
		exit;
	    default:
		return $xml;
		exit;
	}
	
    }
    
    /**
     * EN: Convert an object into array
     * ID: Konversi dari object ke array
     * 
     * @param object
     */
    public static function object_to_array($object){
	
        if( ! is_object($object) && ! is_array($object) )
            return $object;
        
        if( is_object($object) )
            $object = get_object_vars($object);
        
        return array_map(array('Library_tools', 'object_to_array'), $object);
    }
    
    /**
     * EN: Convert an array into object
     * ID: Konversi dari array ke object
     *
     * @param array
     */
    public static function array_to_object($var) {
        
        if( is_array($var) ) {
            
            $object = new stdClass();
            foreach($var as $key => $val)
                $object->$key = self::array_to_object($val);
            
            return $object;
        }
        
        return $var;
    }
    
    /**
     * Initiate contoller within contoller
     *
     * @author kandar
     * @return void
     * @param string $controller Controller's name
     * @param array $params The parameters for it controller
     * @param string $alias_method a method name for alias call
     */
    public static function sub_controller( $controller, $params = array(), $alias_method = false ){
        
        $requsts = array();
        
        if( ! empty($params[1]) )
            $requsts = array_slice($params, 1);
        
        $controller = 'Controller_'.$controller;
        $controller = new $controller();
        
        // Initiate Default Method
        $method = 'index';
        
        if( ! empty($params[0]) )
            $method = $params[0];
        
        if( ! is_callable(array($controller, $method)) ){
            
            if( ! $alias_method )
                Library_notice::_404();
            
            $requsts = ( $requsts ) ? array_merge(array($method), $requsts) : array($method);
             $method = $alias_method;
        }
        
       call_user_func_array(array($controller, $method), $requsts);
    }
    
} // End tools class