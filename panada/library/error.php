<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada error template.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */

class Library_error {
    
    /**
     * ID: Kirimkan pesan error ke halaman error template
     * EN: Send error message to error template file
     *
     * @param string $file
     */
    public function error_template_view( $data = array() ){
	
	$defaults = array(
	    'error_code' => 404,
	    'header_page_title' => 'Page Not Found - Error 404',
	    'template_file' => '40x',
	    'content' => '<h1>Error: Page not Found!</h1>'
	);
	
	$data = array_merge($defaults, $data);
	extract( $data, EXTR_SKIP );
	
	Library_tools::set_status_header($error_code);
	
	$path = APPLICATION . 'view/error_templates/' . $template_file . '.php';
	
	include_once $path;
	exit;
    }
    
    /**
     * EN: Error 404 tempalate.
     *
     * @return string
     */
    public function _404(){
        self::error_template_view();
    }
    
    /**
     * EN: Error 400 tempalate.
     *
     * @return string
     */
    public function _400(){
	
	$data = array(
	    'error_code' => 400,
	    'header_page_title' => 'Bad Request - Error 400',
	    'template_file' => '40x',
	    'content' => '<h1>Error: Bad Request!</h1>'
	);
	
	self::error_template_view($data);
    }
    
     /**
     * EN: Error 500 tempalate.
     *
     * @param integer
     * @param string
     * @return string
     */
    public function _500($message = '<h1>Internal Server Error</h1>', $title = 'Internal Server Error'){
	
	$data = array(
	    'error_code' => 500,
	    'header_page_title' => $title,
	    'template_file' => '50x',
	    'content' => $message
	);
	
	self::error_template_view($data);
    }
    
     /**
     * EN: Template untuk error database.
     *
     * @param string
     * @return string
     */
    public function database($message = ''){
	
	$body = '<h2>Error: Database</h2>';
	$body .= '<p>'.$message.'</p>';
	
	self::_500($body);
    }
    
     /**
     * ID: Template untuk error costume.
     *
     * @param int
     * @param string
     * @return string
     */
    public function costume($code = 200, $message = '', $page_title = ''){
	
	$data = array(
	    'error_code' => $code,
	    'header_page_title' => $page_title,
	    'template_file' => '50x',
	    'content' => $message
	);
	
	self::error_template_view($data);
        
    }
    
    /**
     * EN: PHP Error handler
     * @param int
     * @param string
     * @param string
     * @param string
     */
    public function error_handler($errno, $errstr, $errfile, $errline){
    
	if (!(error_reporting() & $errno)) {
	    // This error code is not included in error_reporting
	    return;
	}
	
	switch ($errno) {
	    case E_USER_ERROR:
		$error_str = "<strong>Error</strong>: $errstr<br />\n";
		break;
		
	    case E_USER_WARNING:
		$error_str = "<strong>Warning</strong>: $errstr, called in $errfile line $errline<br />\n";
		break;
		
	    case E_USER_NOTICE:
		$error_str = "<strong>Notice</strong>: $errstr, called in $errfile line $errline<br />\n";
		break;
		
	    default:
		$error_str = "<strong>Warning</strong>: $errstr, called in $errfile line $errline<br />\n";
		break;
	}
	
	$error_str .= "<strong>Backtrace</strong>: ".self::get_caller()."<br />\n";
	
	// Write the error to log file
	@error_log($error_str, 0);
	
	if( $errno == E_USER_ERROR )
	    self::_500($error_str, 'Internal Server Error');
	
	echo $error_str;
	
	/* Don't execute PHP internal error handler */
	return true;
    }
    
    /**
     * EN: generates class/method backtrace
     * @param integer
     * @return array
     */
    public function get_caller($offset = 1) {
        
	$caller = array();
        $bt = debug_backtrace(false);
	$bt = array_slice($bt, $offset);
        $bt = array_reverse( $bt );
	
        foreach ( (array) $bt as $call ) {
	    
	    if ( ! isset( $call['class'] ) )
		continue;
	    
            if ( @$call['class'] == __CLASS__ )
                continue;
	    
	    $function = $call['class'] . '->'.$call['function'];
        
	    if( isset($call['line']) )
		$function .= ' line '.$call['line'];
	    
            $caller[] = $function;
        }
	
        $caller = implode( ', ', $caller );
	
        return $caller;
    }
    
}// End Library_error