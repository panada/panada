<?php
namespace Resources;

class HttpException extends \Exception {
    
    public function __construct($message = null, $code = 0, Exception $previous = null) {
        
        set_exception_handler( array($this, 'main') );
        parent::__construct($message, $code, $previous);
    }
    
    public function __toString() {
        
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
    
    public function main($exception){
        
        self::outputError($exception->getMessage());
    }
    
    public static function outputError($message = null){
        
        // Write the error to log file
	@error_log('Error 404 Page Not Found: '.$_SERVER['REQUEST_URI']);
        
        header('HTTP/1.1 404 Not Found', true, 500);
        \Resources\Controller::outputError('errors/404', array('message' => $message) );
    }
}