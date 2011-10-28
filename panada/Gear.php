<?php
class Gear {
    
    public static function loader($file){
        
        $prefix = explode('\\', $file);
        
        switch ( $prefix[0] ) {
            case 'Resources':
                $folder = GEAR;
                break;
            default:
                $folder = APP;
                break;
        }
        
        if( ! file_exists( $file = $folder . str_ireplace('\\', '/', $file) . '.php' ) )
            die('Error 500 - Resource not available!');
        
        include $file;
    }
    
    public static function main(){
        
        spl_autoload_register('Gear::loader');
        
        $uri                = new Resources\Uri;
        $controllerClass    = ucwords( $uri->getClass() );
        $controller         = 'Controllers\\' . $controllerClass;
        
        if( ! file_exists( APP . 'Controllers/' . $controllerClass . '.php' ) ){
            die('Error 404 - Controller not exists!');
        }
        
        $method = $uri->getMethod();
        
        if( ! $request = $uri->getRequests() )
            $request = array();
        
        $instance = new $controller;
        
        if( ! method_exists($instance, $method) )
            die('Error 404 - Method not exists!');
        
        call_user_func_array(array($instance, $method), $request);
    }
}