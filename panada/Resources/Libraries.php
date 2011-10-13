<?php
namespace Resources;

class Libraries {
    
    public function __get($name){
        
        $class = 'Libraries\\'.ucwords($name);
        $object = new $class;
        $object->libraries = new Libraries;
        $object->models = new Models;
        
        return new $object;
    }
}