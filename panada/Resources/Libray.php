<?php
namespace Resources;

class Library {
    
    public function __get($name){
        
        $class = 'Libraries\\'.ucwords($name);
        $object = new $class;
        $object->library = new Library;
        $object->model = new Model;
        
        return new $object;
    }
}