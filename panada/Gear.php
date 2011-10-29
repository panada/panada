<?php
class Gear {
    
    private $uriObj, $config, $firstUriPath;
    
    public function __construct(){
        
        spl_autoload_register( array($this, 'loader') );
        
        $this->config['main']   = Resources\Config::main();
        $this->uriObj           = new Resources\Uri;
        $this->firstUriPath     = ucwords( $this->uriObj->getClass() );
        $controllerNamespace    = 'Controllers\\' . $this->firstUriPath;
        
        if( ! file_exists( APP . 'Controllers/' . $this->firstUriPath . '.php' ) ){
            $this->controllerHandler();
            return;
        }
        
        $method = $this->uriObj->getMethod();
        
        if( ! $request = $this->uriObj->getRequests() )
            $request = array();
        
        $instance = new $controllerNamespace;
        
        if( ! method_exists($instance, $method) ){
            
            $request = array_slice( $this->uriObj->path(), 1);
            $method = $this->config['main']['alias']['method'];
            
            if( ! method_exists($instance, $method) )
                die('Error 404 - Method '.$method.' not exists!');
        }
        
        $this->run($instance, $method, $request);
    }
    
    
    private function loader($file){
        
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
            die('Error 500 - Resource: '.$file.' not available!');
        
        include $file;
    }
    
    private function controllerHandler(){
        
        /**
         * Cek apakah alias controller di set di konfigurasi.
         * Jika tidak, adakah sub controller?
         * Jika tidak apakah module dengan nama ini ada?
         */
        if( isset($this->config['main']['alias']['controller']['class']) ){
            
            $controller = $this->config['main']['alias']['controller']['class'];
            $method     = $this->config['main']['alias']['controller']['method'];
            $instance   = new $controller;
            $request    = $this->uriObj->path();
            
            $this->run($instance, $method, $request);
            return;
        }
        
        $this->subControllerHandler();
    }
    
    private function subControllerHandler(){
        
        $controllerClass    = ucwords( $this->uriObj->getMethod() );
        
        if( ! file_exists( APP . 'Controllers/' . $this->firstUriPath . '/' . $controllerClass . '.php') ){
            $this->moduleHandler();
            return;
            //die('Error 404 - Sub-controller not exists!');
        }
        
        $controllerNamespace    = 'Controllers\\' . $this->firstUriPath . '\\' .$controllerClass;
        $instance               = new $controller;
        $request                = array_slice( $this->uriObj->path(), 3);
        
        if( ! $method = $this->uriObj->path(2) )
            $method = 'index';
        
        $this->run($instance, $method, $request);
    }
    
    private function moduleHandler(){
        
        if ( ! is_dir( $this->config['main']['module']['path'] . $this->firstUriPath . '/' ) )
            die('Error 404 - Module '.$this->firstUriPath.' not exists!');
    }
    
    private function run($instance, $method, $request){
        
        call_user_func_array(array($instance, $method), $request);
    }
}