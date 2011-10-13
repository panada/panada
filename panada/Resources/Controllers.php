<?php
namespace Resources;

class Controllers {
    
    public function __get($class){
        
        $class = str_ireplace(
                    array(
                        'models',
                        'libraries'
                    ),
                    array(
                        'Resources\Models',
                        'Resources\Libraries',
                    ),
                    $class
                );
        
        return new $class;
    }
    
    public function output( $filePath, $data = array() ){
        
        $filePath = APPS.'views/'.$filePath;
        
        if( ! file_exists($viewFile = $filePath.'.php') )
            die('500');
        
        if( ! empty($data) )
            $this->view = $data;
        
        if( ! empty($this->view ) )
            extract( $this->view, EXTR_SKIP );
        
        include_once $viewFile;
    }
}