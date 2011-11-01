<?php
namespace Resources;

class ErrorExceptions extends \Exception {
    
    public function __construct($message, $code = 0, Exception $previous = null) {
        
        parent::__construct($message, $code, $previous);
        
        //$this->main();
    }
    
    public function __toString() {
        
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
    
    public static function main($exception){
        
        $trace = $exception->getTrace();
        $fileString = file_get_contents($trace[2]['file']);
        $getLine = explode("\n", $fileString);
        
        header("HTTP/1.1 500 Internal Server Error", true, 500);
        
        echo '<h2>Runtime Error!</h2>';
        echo '<strong>Error in file</strong>: '.$trace[2]['file'].' Line: '.$trace[2]['line'].'<br />';
        echo '<strong>Error message</strong>: '.$exception->getMessage().'<br />' . "\n";
        
        echo '<pre>';
        
        for($i = 5; $i <= 15; $i++){
            $line = $i+1;
            
            echo '<strong>'.$line.'</strong>';
            
            if($trace[2]['line'] == $line )
                echo '<span style="color:#DD0000">'.$getLine[$i] . "</span>\n";
            else
                echo $getLine[$i] . "\n";
        }
        
        echo '</pre>';
        
        exit(1);
    }
}