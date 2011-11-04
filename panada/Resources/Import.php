<?php
namespace Resources;

class Import {
    
    public static function vendor($filePath, $className = false, $args = array()){
        
        if( ! file_exists( $file = GEAR . 'vendors/'.$filePath.'.php' ) )
            return false;
        
        include_once $file;
        
        if( ! $className ){
            $arr = explode('/', $filePath);
            $className = end( $arr );
        }
        
        if( empty($args) )
            return new $className;
        
        $reflector = new \ReflectionClass($className);
        return $reflector->newInstanceArgs($args);
    }
}