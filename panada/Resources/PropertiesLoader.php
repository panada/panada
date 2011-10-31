<?php
namespace Resources;

class PropertiesLoader {
    
    private $childNamespace, $classNamespace;
    
    public function __construct($childNamespace, $classNamespace){
        
        $this->childNamespace = $childNamespace;
        $this->classNamespace = $classNamespace;
    }
    
    public function __get($name){
        
        $class = $this->classNamespace.'\\'.ucwords($name);
        
        if( $this->childNamespace[0] == 'Modules' )
            $class = $this->childNamespace[0].'\\'.$this->childNamespace[1].'\\'.$class;
        
        $object = new $class;
        $object->library = new PropertiesLoader( $this->childNamespace, 'libray' );
        $object->model = new PropertiesLoader( $this->childNamespace, 'model' );
        
        return $object;
    }
}