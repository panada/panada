<?php
namespace Resources;

class Controller {
    
    private $childNamespace, $viewCache;
    
    public function __construct(){
        
        $child = get_class($this);
        
        $this->childClass = array(
                            'namespaceArray' => explode( '\\', $child),
                            'namespaceString' => $child
                        );
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
        
        return new $class($this->childClass['namespaceArray']);
    }
    
    public function output( $file, $data = array(), $isReturnValue = false ){
        
        $filePath = APP.'views/'.$file;
        
        if( $this->childClass['namespaceArray'][0] == 'Modules' ){
            $mainConfig = Config::main();
            $filePath = $mainConfig['module']['path'].$this->childClass['namespaceArray'][0].'/'.$this->childClass['namespaceArray'][1].'/views/'.$file;
        }
        
        if( ! file_exists($viewFile = $filePath.'.php') )
            die('Error 500: No view file.');
        
        if( ! empty($data) ){
            $this->viewCache = array(
                'data' => $data,
                'prefix' => $this->childClass['namespaceString'],
            );
        }
        
        if( ! empty($this->viewCache) && $this->viewCache['prefix'] == $this->childClass['namespaceString'] )
            extract( $this->viewCache['data'], EXTR_SKIP );
        
        include_once $viewFile;
    }
    
    public function outputJSON($data, $isReturnValue = false){
        
    }
    
    public function outputXML($data, $isReturnValue = false){
        
    }
}