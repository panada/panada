<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada Restful class.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */

class Library_rest {
    
    public $request_method;
    public $request_data		= array();
    public $set_request_headers		= array();
    public $response_status;
    public $response_output_header	= false;
    public $timeout			= 30;
    
    public function get_request(){
        
        $this->request_method = strtoupper($_SERVER['REQUEST_METHOD']);
        
        switch ($this->request_method){
            case 'GET':
                $this->request_data = $_GET;
                break;
            case 'POST':
                $this->request_data = $_POST;
                break;
            case 'PUT':
                $this->request_data = $this->get_php_input();
                break;
	    case 'DELETE':
                $this->request_data = $this->get_php_input();
                break;
        }
        
        return $this->request_data;
    }
    
    //EN: See this trick at http://www.php.net/manual/en/function.curl-setopt.php#96056
    private function get_php_input(){
	
	parse_str(file_get_contents('php://input'), $put_vars);
        return $put_vars;
    }
    
    public function send_request($uri, $method = 'GET', $data = array()){
        
	if( ! function_exists('curl_init') )
	    Library_error::_500('Error: No PHP cUrl found!', '<h1>Error: REST Library required PHP cUrl modul!</h1>');
	
	$this->set_request_headers[]	= 'User-Agent: Panada PHP Framework REST API/0.1';
	$method				= strtoupper($method);
        $url_separator			= ( parse_url( $uri, PHP_URL_QUERY ) ) ? '&' : '?';
        $uri				= ( $method == 'GET' && ! empty($data) ) ? $uri . $url_separator . http_build_query($data) : $uri;
        $c				= curl_init();
	
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_URL, $uri);
	curl_setopt($c, CURLOPT_TIMEOUT, $this->timeout);
        
        if($this->response_output_header)
            curl_setopt($c, CURLOPT_HEADER, true);
	
        if( ! empty($data) && $method != 'GET' ) {
	    
	    if( $method == 'POST' )
		curl_setopt($c, CURLOPT_POST, true);
	    
	    if( $method == 'PUT' || $method == 'DELETE' ) {
		
		$data				= http_build_query($data);
		$this->set_request_headers[]	= 'Content-Length: ' . strlen($data);
		
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, $method);
	    }
	    
	    curl_setopt($c, CURLOPT_POSTFIELDS, $data);
        }
	
	curl_setopt($c, CURLOPT_HTTPHEADER, $this->set_request_headers);
	
        $contents = curl_exec($c);
	$this->response_status = curl_getinfo($c, CURLINFO_HTTP_CODE);
        
        curl_close($c);
	
        if($contents)
	    return $contents;
        
        return false;
    }
    
    public function set_response_header($code = 200){
	
	Library_tools::set_status_header($code);
    }
    
    public function wrap_response_output($data, $format = 'json'){
        
        header('Content-type: application/' . $format);
	
	if($format == 'xml')
	    return Library_tools::xml_encode($data);
	else
	    return json_encode($data);
    }
    
} // End Rest Class