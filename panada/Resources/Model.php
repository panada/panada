<?php
namespace Resources;

class Model {
    
    public function __get($name){
        
        $class = 'Models\\'.ucwords($name);
        $object = new $class;
        $object->library = new Library;
        $object->model = new Model;
        
        return $object;
    }
}