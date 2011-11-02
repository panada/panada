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
        
        header('HTTP/1.1 404 Not Found', true, 500);
        
        $html = '<h2>Page not found!</h2>';
        $html .= 'Message: '.$message;
        
        die($html);
    }
}