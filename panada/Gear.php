<?php
class Gear {
    
    private $uriObj, $config, $firstUriPath;
    
    public function __construct(){
        
        spl_autoload_register( array($this, 'loader') );
        
        $this->disableMagicQuotes();
        
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
            case 'Modules':
                $folder = $this->config['main']['module']['path'];
                break;
            default:
                $folder = APP;
                break;
        }
        
        if( ! file_exists( $file = $folder . str_ireplace('\\', '/', $file) . '.php' ) )
            die('Error 500 - Resource: '.$file.' not available!');
        
        include $file;
    }
    
    private function disableMagicQuotes(){
        
        if ( get_magic_quotes_gpc() ) {
            array_walk_recursive($_GET,  array($this, 'stripslashesGpc') );
            array_walk_recursive($_POST, array($this, 'stripslashesGpc') );
            array_walk_recursive($_COOKIE, array($this, 'stripslashesGpc') );
            array_walk_recursive($_REQUEST, array($this, 'stripslashesGpc') );
        }
    }
    
    private function stripslashesGpc(&$value){
        $value = stripslashes($value);
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
        $instance               = new $controllerNamespace;
        $request                = array_slice( $this->uriObj->path(), 3);
        
        if( ! $method = $this->uriObj->path(2) )
            $method = 'index';
        
        if( ! method_exists($instance, $method) )
            die('Error 404 - Method '.$method.' not exists!');
        
        $this->run($instance, $method, $request);
    }
    
    private function moduleHandler(){
        
        if ( ! is_dir( $moduleFolder = $this->config['main']['module']['path'] . 'Modules/'. $this->firstUriPath . '/' ) )
            die('Error 404 - Module '.$this->firstUriPath.' not exists!');
        
        if( ! $controllerClass = $this->uriObj->path(1) )
            $controllerClass = 'Home';
        
        $controllerClass = ucwords( $controllerClass );
        
        // Pastikan apakah file untuk class ini tersedia.
        if( ! file_exists( $moduleFolder . 'Controllers/' . $controllerClass . '.php' ) )
            die('file controller module tidak tersedia.');
        
        $controllerNamespace    = 'Modules\\'.$this->firstUriPath.'\Controllers\\'.$controllerClass;
        $instance               = new $controllerNamespace;
        $request                = array_slice( $this->uriObj->path(), 3);
        
        if( ! $method = $this->uriObj->path(2) )
            $method = 'index';
        
        if( ! method_exists($instance, $method) )
            die('Error 404 - Method '.$method.' not exists!');
        
        $this->run($instance, $method, $request );
    }
    
    private function run($instance, $method, $request){
        
        call_user_func_array(array($instance, $method), $request);
    }
}