<?php
/**
 * Gear Class File
 *
 * This is the heart of the whole Panada system.
 *
 * @author Iskandar Soesman <k4ndar@yahoo.com>
 * @link http://panadaframework.com/
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @since version 1.0.0
 */

class Gear {
    
    private $uriObj, $config, $firstUriPath;
    
    /**
     * Preparation step before anything else.
     *
     * @return void
     */
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
                throw new Resources\HttpException('Method '.$method.' does not exists in controller '.$this->firstUriPath.'.');
        }
        
        $this->run($instance, $method, $request);
    }
    
    /**
     * Magic loader to load instantiated class.
     *
     * @param string $file Class namespace.
     * @return void
     */
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
            throw new Resources\RunException('Resource '.$file.' not available!');
        
        include $file;
    }
    
    /**
     * We don't need Magic Quotes, let's kill it.
     *
     * @return void
     */
    private function disableMagicQuotes(){
        
        if ( get_magic_quotes_gpc() ) {
            array_walk_recursive($_GET,  array($this, 'stripslashesGpc') );
            array_walk_recursive($_POST, array($this, 'stripslashesGpc') );
            array_walk_recursive($_COOKIE, array($this, 'stripslashesGpc') );
            array_walk_recursive($_REQUEST, array($this, 'stripslashesGpc') );
        }
    }
    
    /**
     * Strip the slash mark.
     *
     * @param string $value
     * @return void
     */
    private function stripslashesGpc(&$value){
        $value = stripslashes($value);
    }
    
    /**
     * Hendle the controller calling process.
     *
     * The steps is:
     *  - Does alias controller are defined in main config?
     *  - If not, is sub-controller exists?
     *  - If not, module with this name exists?
     *  - If all fault, then throw 404.
     *
     *  @return void
     */
    private function controllerHandler(){
        
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
    
    /**
     * Hendle the sub-controller calling process.
     *
     * @return void
     */
    private function subControllerHandler(){
        
        $controllerClass    = ucwords( $this->uriObj->getMethod() );
        
        if( ! file_exists( APP . 'Controllers/' . $this->firstUriPath . '/' . $controllerClass . '.php') ){
            $this->moduleHandler();
            return;
        }
        
        $controllerNamespace    = 'Controllers\\' . $this->firstUriPath . '\\' .$controllerClass;
        $instance               = new $controllerNamespace;
        $request                = array_slice( $this->uriObj->path(), 3);
        
        if( ! $method = $this->uriObj->path(2) )
            $method = 'index';
        
        if( ! method_exists($instance, $method) )
            throw new Resources\HttpException('Method '.$method.' does not exists in controller /'.$this->firstUriPath.'/'.$controllerClass.'.');
        
        $this->run($instance, $method, $request);
    }
    
    /**
     * Hendle the module calling process.
     *
     * @return void
     */
    private function moduleHandler(){
        
        if ( ! is_dir( $moduleFolder = $this->config['main']['module']['path'] . 'Modules/'. $this->firstUriPath . '/' ) )
            throw new Resources\HttpException('Module '.$this->firstUriPath.' does not exists');
        
        if( ! $controllerClass = $this->uriObj->path(1) )
            $controllerClass = 'Home';
        
        $controllerClass = ucwords( $controllerClass );
        
        // Does this class's file exists?
        if( ! file_exists( $file = $moduleFolder . 'Controllers/' . $controllerClass . '.php' ) )
            throw new Resources\RunException('Resource '.$file.' not available!');
        
        $controllerNamespace    = 'Modules\\'.$this->firstUriPath.'\Controllers\\'.$controllerClass;
        $instance               = new $controllerNamespace;
        $request                = array_slice( $this->uriObj->path(), 3);
        
        if( ! $method = $this->uriObj->path(2) )
            $method = 'index';
        
        if( ! method_exists($instance, $method) )
            throw new Resources\HttpException('Method '.$method.' does not exists in controller '.$moduleFolder);
            
        
        $this->run($instance, $method, $request );
    }
    
    /**
     * Call the controller's method
     *
     * @param object $instance
     * @param string $method
     * @param array $request
     * @return void
     */
    private function run($instance, $method, $request){
        
        call_user_func_array(array($instance, $method), $request);
    }
}