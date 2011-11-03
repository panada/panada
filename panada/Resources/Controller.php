<?php
namespace Resources;

class Controller {
    
    private $childNamespace, $viewCache, $viewFile;
    
    public function __construct(){
        
        $child = get_class($this);
        
        $this->childClass = array(
                            'namespaceArray' => explode( '\\', $child),
                            'namespaceString' => $child
                        );
    }
    
    public function __get($class){
        
        $classNamespace = array(
            'model' => 'Models',
            'Model' => 'Models',
            'models' => 'Models',
            'Models' => 'Models',
            'library' => 'Libraries',
            'Library' => 'Libraries',
            'libraries' => 'Libraries',
            'Libraries' => 'Libraries',
        );
        
        try{
            if( ! isset($classNamespace[$class]) )
                throw new \Exception('Undefined property '.$class);
        }
        catch(\Exception $e){
            $arr = $e->getTrace();
            RunException::outputError($e->getMessage(), $arr[0]['file'], $arr[0]['line']);
        }
        
        
        return new PropertiesLoader($this->childClass['namespaceArray'], $classNamespace[$class]);
    }
    
    public static function outputError($file, $data = array(), $isReturnValue = false){
        
        $controller = new Controller;
        $controller->output($file, $data, $isReturnValue);
    }
    
    public function output( $panadaViewfile, $data = array(), $isReturnValue = false ){
        
        $panadaFilePath = APP.'views/'.$panadaViewfile;
        
        if( $this->childClass['namespaceArray'][0] == 'Modules' ){
            $mainConfig = Config::main();
            $panadaFilePath = $mainConfig['module']['path'].$this->childClass['namespaceArray'][0].'/'.$this->childClass['namespaceArray'][1].'/views/'.$panadaViewfile;
        }
        
        try{
            if( ! file_exists($this->viewFile = $panadaFilePath.'.php') )
                throw new \Resources\RunException('View file in '.$panadaViewFile.' does not exits');
        }
        catch(\Resources\RunException $e){
            $arr = $e->getTrace();
            RunException::outputError($e->getMessage(), $arr[0]['file'], $arr[0]['line']);
        }
        
        if( ! empty($data) ){
            $this->viewCache = array(
                'data' => $data,
                'prefix' => $this->childClass['namespaceString'],
            );
        }
        
        // We don't need this variables anymore.
        unset($panadaViewFile, $data, $panadaFilePath);
        
        if( ! empty($this->viewCache) && $this->viewCache['prefix'] == $this->childClass['namespaceString'] )
            extract( $this->viewCache['data'], EXTR_SKIP );
        
        include_once $this->viewFile;
    }
    
    public function outputJSON($data, $isReturnValue = false){
        
    }
    
    public function outputXML($data, $isReturnValue = false){
        
    }
}