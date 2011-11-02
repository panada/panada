<?php
namespace Resources;

class RunException extends \Exception {
    
    public function __construct($message = null, $code = 0, Exception $previous = null) {
        
        set_exception_handler( array($this, 'main') );
        parent::__construct($message, $code, $previous);
    }
    
    public function __toString() {
        
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
    
    public function main($exception){
        
        $trace = $exception->getTrace();
        self::outputError($exception->getMessage(), $trace[2]['file'], $trace[2]['line']);
    }
    
    public static function errorHandlerCallback($errno, $message, $file, $line){
        
        self::outputError($message, $file, $line);
    }
    
    public static function outputError($message, $file, $line){
        
        if( array_search( 'views', explode('/', $file) ) !== false){
            die('Error '.$message.' in '.$file . ' line: ' . $line);
        }
        
        $fileString     = file_get_contents($file);
        $arrLine        = explode("\n", $fileString);
        $totalLine      = count($arrLine);
        $getLine        = array_combine(range(1, $totalLine), array_values($arrLine));
        $startIterate   = $line - 5;
        $endIterate     = $totalLine + 5;
        
        if($startIterate < 0)
            $startIterate  = 0;
        
        if($endIterate > $totalLine)
            $endIterate = $totalLine;
        
        //if ( ! error_reporting() )
        //    return;
        
        $data = array(
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'code' => array()
        );
        
        for($i = $startIterate; $i <= $endIterate; $i++){
            
            $html = '<span style="margin-right:10px;background:#CFCFCF;">'.$i.'</span>';
            
            if($line == $i )
                $html .= '<span style="color:#DD0000">'.$getLine[$i] . "</span>\n";
            else
                $html .= $getLine[$i] . "\n";
                
            $data['code'][] = $html;
        }
        
        header("HTTP/1.1 500 Internal Server Error", true, 500);
        
        \Resources\Controller::outputError('errors/500', $data);
        exit(1);
    }
}