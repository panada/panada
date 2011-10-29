<?php
class Gear {
    
    private static $uriObj, $config, $firstUriPath;
    
    
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
        
        self::$config['main']   = Resources\Config::main();
        self::$uriObj           = new Resources\Uri;
        self::$firstUriPath     = ucwords( self::$uriObj->getClass() );
        $controllerNamespace    = 'Controllers\\' . self::$firstUriPath;
        
        if( ! file_exists( APP . 'Controllers/' . self::$firstUriPath . '.php' ) ){
            self::controllerHandler();
            return;
        }
        
        $method = self::$uriObj->getMethod();
        
        if( ! $request = self::$uriObj->getRequests() )
            $request = array();
        
        $instance = new $controllerNamespace;
        
        if( ! method_exists($instance, $method) ){
            
            $request = array_slice( self::$uriObj->path(), 1);
            $method = self::$config['main']['alias']['method'];
            
            if( ! method_exists($instance, $method) )
                die('Error 404 - Method not exists!');
        }
        
        self::run($instance, $method, $request);
    }
    
    private static function controllerHandler(){
        
        /**
         * Cek apakah alias controller di set di konfigurasi.
         * Jika tidak, adakah sub controller?
         * Jika tidak apakah module dengan nama ini ada?
         */
        if( isset(self::$config['main']['alias']['controller']['class']) ){
            
            $controller = self::$config['main']['alias']['controller']['class'];
            $method     = self::$config['main']['alias']['controller']['method'];
            $instance   = new $controller;
            $request    = self::$uriObj->path();
            
            self::run($instance, $method, $request);
            return;
        }
        
        self::subControllerHandler();
    }
    
    private static function subControllerHandler(){
        
        $controllerClass    = ucwords( self::$uriObj->getMethod() );
        
        if( ! file_exists( APP . 'Controllers/' . self::$firstUriPath . '/' . $controllerClass . '.php') ){
            self::moduleHandler();
            return;
            //die('Error 404 - Sub-controller not exists!');
        }
        
        $controllerNamespace    = 'Controllers\\' . self::$firstUriPath . '\\' .$controllerClass;
        $instance               = new $controller;
        $request                = array_slice( self::$uriObj->path(), 3);
        
        if( ! $method = self::$uriObj->path(2) )
            $method = 'index';
        
        self::run($instance, $method, $request);
    }
    
    private static function moduleHandler(){
        
        if ( ! is_dir( self::$config['module']['path'] . $pan_uri->get_class() . '/' ) )
            die('Error 404 - Module not exists!');
    }
    
    private static function run($instance, $method, $request){
        
        call_user_func_array(array($instance, $method), $request);
    }
}