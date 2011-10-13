<?php
namespace Resources;

class Models {
    
    public function __get($name){
        
        $class = 'Models\\'.ucwords($name);
        $object = new $class;
        $object->libraries = new Libraries;
        $object->models = new Models;
        
        return $object;
    }
}