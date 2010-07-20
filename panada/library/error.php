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
     * ID: Membuat instance dari class Library_error
     *
     * @return object
     */
    public static function instance(){
	return new Library_error();
    }
    
    /**
     * ID Template header untuk pesan error.
     *
     * @param integer
     * @return string
     */
    private function header($code){
        
        $return = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd" >
                    <html lang="en"><head><title>Error: '.$code.'</title></head>
                    <body>';
       
        printf($return);
    }
    
    /**
     * ID Template footer untuk pesan error.
     *
     * @return string
     */
    private function footer(){
        
        printf('</body></html>');
    }
    
    /**
     * ID: Error 404 tempalate.
     *
     * @return string
     */
    public function _404(){
        
        Library_tools::set_status_header(404);
        self::header(404);
        echo '<h1>Error: Page not Found!</h1>';
        self::footer();
        exit;
    }
    
     /**
     * ID: Error 400 tempalate.
     *
     * @return string
     */
    public function _400(){
        
        Library_tools::set_status_header(400);
        self::header(400);
        echo '<h1>Error: Bad Request!</h1>';
        self::footer();
        exit;
    }
    
     /**
     * ID: Error 500 tempalate.
     *
     * @param integer
     * @param string
     * @return string
     */
    public function _500($title = '500', $message = '<h1>Error: Bad Request!</h1>'){
	
	Library_tools::set_status_header(500);
        self::header($title);
        echo $message;
        self::footer();
        exit;
    }
    
     /**
     * ID: Template untuk error database.
     *
     * @param string
     * @return string
     */
    public function database($message = ''){
	
	$title = 'Database';
	$body = '<h2>Error: Database</h2>';
	$body .= '<p>'.$message.'</p>';
	
	self::_500($title, $body);
    }
    
     /**
     * ID: Template untuk error costume.
     *
     * @param int
     * @param string
     * @return string
     */
    public function costume($code = 200, $message = ''){
	
	Library_tools::set_status_header($code);
        self::header($code);
        echo $message;
        self::footer();
        
    }
    
}// End Library_error