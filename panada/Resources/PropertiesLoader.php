<?php
/**
 * Properties Loader.
 *
 * @author  Iskandar Soesman <k4ndar@yahoo.com>
 * @link    http://panadaframework.com/
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @since   version 1.0.0
 * @package Resources
 */
namespace Resources;

class PropertiesLoader {
    
    private $childNamespace, $classNamespace;
    
    public function __construct($childNamespace, $classNamespace){
        
        $this->childNamespace = $childNamespace;
        $this->classNamespace = $classNamespace;
    }
    
    public function __call( $name, $arguments = array() ){
        
        $class = $this->classNamespace.'\\'.ucwords($name);
        
        if( $this->childNamespace[0] == 'Modules' )
            $class = $this->childNamespace[0].'\\'.$this->childNamespace[1].'\\'.$class;
        
        $reflector = new \ReflectionClass($class);
        
        // Lets try this class's constructor.
        try{
            $object = $reflector->newInstanceArgs($arguments);
        }
        catch(\ReflectionException $e){
            $object = new $class;
        }
        
        $object->library    = new PropertiesLoader( $this->childNamespace, 'Libraries' );
        $object->model      = new PropertiesLoader( $this->childNamespace, 'Models' );
        $object->Library    = clone $object->library;
        $object->libraries  = clone $object->library;
        $object->Libraries  = clone $object->library;
        $object->Model      = clone $object->model;
        $object->models     = clone $object->model;
        $object->Models     = clone $object->model;
        
        return $object;
    }
    
    public function __get($name){
        return $this->__call($name);
    }
}