<?php
namespace Resources;

class Controller {
    
    private $childClass;
    
    public function __construct(){
        
        $this->childClass = get_class($this);
    }
    
    public function __get($class){
        
        $class = str_ireplace(
                    array(
                        'model',
                        'library'
                    ),
                    array(
                        'Resources\Model',
                        'Resources\Libray',
                    ),
                    $class
                );
        
        return new $class;
    }
    
    public function output( $filePath, $data = array() ){
        
        $filePath = APP.'views/'.$filePath;
        
        if( ! file_exists($viewFile = $filePath.'.php') )
            die('500');
        
        if( ! empty($data) )
            $this->view = $data;
        
        if( ! empty($this->view ) )
            extract( $this->view, EXTR_SKIP );
        
        include_once $viewFile;
    }
}