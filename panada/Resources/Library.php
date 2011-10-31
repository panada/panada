<?php
namespace Resources;

class Library {
    
    private $childNamespace;
    
    public function __construct($childNamespace){
        
        $this->childNamespace = $childNamespace;
    }
    
    public function __get($name){
        
        $class = 'Libraries\\'.ucwords($name);
        
        if( $this->childNamespace[0] == 'Modules' )
            $class = $this->childNamespace[0].'\\'.$this->childNamespace[1].'\\'.$class;
        
        $object = new $class;
        $object->library = new Library($this->childNamespace);
        $object->model = new Model($this->childNamespace);
        
        return $object;
    }
}