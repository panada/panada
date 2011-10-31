<?php
namespace Resources;

class Model {
    
    private $childNamespace;
    
    public function __construct($childNamespace){
        
        $this->childNamespace = $childNamespace;
    }
    
    public function __get($name){
        
        $class = 'Models\\'.ucwords($name);
        
        if( $this->childNamespace[0] == 'Modules' )
            $class = $this->childNamespace[0].'\\'.$this->childNamespace[1].'\\'.$class;
        
        $object = new $class;
        $object->library = new Library($this->childNamespace);
        $object->model = new Model($this->childNamespace);
        
        return $object;
    }
}