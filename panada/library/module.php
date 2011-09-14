<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Module class parent.
 * Every module controller should be child of this class.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.3
 */

class Panada_module extends Panada {
    
    static public $_module_name;
    
    public function __construct(){
        
        parent::__construct();
        
        spl_autoload_register(array($this, 'component_loader'));
        spl_autoload_register('__autoload');
        
        $this->base_path = GEAR . 'module/' . self::$_module_name . '/';
    }
    
    public function component_loader($class){
        
        $class      = strtolower($class);
        $file_name  = explode('_', $class);
        $components = array('library', 'model');
        $module_name= false;
        
        foreach($components as $component){
            
            $key = array_search($component, $file_name);
            
            if( $key !== false ){
                
                $module_name    = array_slice($file_name, 0, $key);
                $module_name    = implode('_', $module_name);
                $class_name     = array_slice($file_name, $key + 1, count($file_name));
                $class_name     = implode('_', $class_name);
                
                break;
            }
        }
        
        if( ! $module_name )
            return false;
        
        $file = GEAR . 'module/'. $module_name . '/' . $component . '/' . $class_name .'.php';
        
        include_once $file;
    }
}
//end Panada_module class


/**
 * Module loader.
 * A class for load a module from main controller.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.3
 */
class Library_module {
    
    private $class_inctance;
    private $module_name;
    
    /**
     * Loader for module
     *
     * @param mix String or array for module configuration
     * @return object
     */
    public function __construct($args = false){
        
        if( ! $args )
            return false;
        
        if( is_string($args) ){
            
            $args = array(
                'name' => $args,
                'controller' => 'home'
            );
        }
        
        $default = array(
            'controller' => 'home'
        );
        
        $module = array_merge($default, $args);
        
        $file = 'module/' . $module['name'] . '/controller/' . $module['controller'];
        
        Panada_module::$_module_name = $module['name'];
        $this->module_name = $module['name'];
        
        $this->load_file($file);
    }
    
    /**
     * Include the actual class file
     *
     * @param string $file_path Absolute path of the file location
     * @return void
     */
    private function load_file($file_path){
        
        $arr = explode('/', $file_path);
        $file_name = end( $arr );
        
        $prefix = $arr[2];
        $file_path = GEAR . $file_path.'.php';
        
        if( ! file_exists( $file_path ) )
            Library_error::_500('<b>Error:</b> No <b>'.$file_name.'</b> file in '.$arr[0].' folder.');
        
        $class = ucwords($this->module_name).'_'.$prefix.'_'.$file_name;
        
        include_once $file_path;
        
        if( ! class_exists($class) )
            Library_error::_500('<b>Error:</b> No class <b>'.$class.'</b> exists in file '.$file_name.'.');
        
        $this->class_inctance = new $class();
        
        //assigning the autoloaded class into each vars object
        if( ! empty($this->config->auto_loader) ) {
            
            $object_vars = get_object_vars($this->class_inctance);
            unset($object_vars['config'], $object_vars['base_url']);
            
            $object_vars = array_keys($object_vars);
            
            $auto_loader = (array) $this->config->auto_loader;
            
            foreach( $auto_loader as $class_name){
                
		$var = Panada::var_name($class_name);
                $class_instance = new $class_name();
                $this->class_inctance->$var = $class_instance;
                
                foreach($object_vars as $object_vars)
                    if($object_vars != $var && is_object($this->class_inctance->$object_vars) )
                        $this->class_inctance->$object_vars->$var = $class_instance;
                
            }
        }
    }
    
    /**
     * Magic method for calling a method from loaded class
     *
     * @param string Method name
     * @param array Method arguments
     * @return mix Method return value
     */
    public function __call($name, $arguments = array() ){
        
        return call_user_func_array( array($this->class_inctance, $name), $arguments );
    }
    
    /**
     * Magic method for getting a property from loaded class
     *
     * @param string Property name
     * @return mix The value of the property
     */
    public function __get($name){
        
        return $this->class_inctance->$name;
    }
    
    /**
     * Magic method for setting a property for loaded class
     *
     * @param string Properties name
     * @param mix the value to store
     * @return void
     */
    public function __set($name, $value){
        
        $this->class_inctance->$name = $value;
    }
    
    /**
     * Hendle routing direct from url. The url
     * should looks: http://website.com/index.php/module_name/controller_name/method_name
     *
     */
    public function public_routing(){
        
        $pan_uri        = new Library_uri();
        $module_name    = $pan_uri->get_class();
        $controller     = $pan_uri->get_method('home');
        $method         = $pan_uri->break_uri_string(3);
        $class          = ucwords($module_name).'_controller_'.$controller;
        
        Panada_module::$_module_name = $module_name;
        
        $module_config  = $this->config(true);
       
        // Does this controller can be accessed via URL?
        if( ! $module_config['allow_url_routing'] )
            Library_error::_404();
        
        $url_routing_is_allowed = true;
        if( is_array($module_config['allow_url_routing']) && ! empty($module_config['allow_url_routing']) ){
            if( array_search($controller, $module_config['allow_url_routing']) === false )
                $url_routing_is_allowed = false; //prev: goto alias_controller;
        }
        
        if( empty($method) && $url_routing_is_allowed )
            $method = 'index';
        
        if( ! $request = $pan_uri->get_requests(4) && $url_routing_is_allowed )
            $request = array();
        
        if( ! file_exists( $file = GEAR . 'module/' . $module_name . '/controller/' . $controller . '.php' ) || !$url_routing_is_allowed ){
            
            // Does alias controller config exists?
            if( empty($module_config['alias_controller']) )
                Library_error::_404();
            
            $controller = array_keys($module_config['alias_controller']);
            $controller = $controller[0];
            $method     = array_values($module_config['alias_controller']);
            $method     = $method[0];
            $class      = ucwords($module_name).'_controller_'.$controller;
            
            if( ! $request = $pan_uri->get_requests(2) )
                $request = array();
            
            if( ! file_exists( $file = GEAR . 'module/' . $module_name . '/controller/' . $controller . '.php' ) )
                Library_error::_500('<b>Error:</b> No alias controller exists in module <b>' . $module_name . '</b>. Check your module configuration.');
            
        }
        
        include_once $file;
        
        $Panada = new $class();
        
        // autoloader
        if( ! empty($Panada->config->auto_loader) ) {
            
            $object_vars = get_object_vars($Panada);
            unset($object_vars['config'], $object_vars['base_url']);
            $object_vars = array_keys($object_vars);
            
            $auto_loader = (array) $Panada->config->auto_loader;
            
            foreach( $auto_loader as $class_name){
                
		$var = Panada::var_name($class_name);
                $class_instance = new $class_name();
                $Panada->$var = $class_instance;
                
                foreach($object_vars as $object_vars)
                    if($object_vars != $var && is_object($Panada->$object_vars) )
                        $Panada->$object_vars->$var = $class_instance;
            }
        }
        
        if( ! method_exists($Panada, $method) ){
            
            $request = ( ! empty($request) ) ? array_merge(array($method), $request) : array($method);
            $method = $Panada->config->alias_method;
            
            if( ! method_exists($Panada, $method) )
                Library_error::_404();
        }
        
        call_user_func_array(array($Panada, $method), $request);
    }
    
    /**
     * Load the module config if exists
     *
     * @param bool
     * @return mix
     */
    public function config($as_array = false){
        
        if( ! file_exists($file = GEAR . 'module/' . Panada_module::$_module_name . '/config.php') )
            return false;
        
        include_once $file;
        
        if( ! isset($CONFIG) || empty($CONFIG) )
            return false;
        
        if( $as_array )
            return $CONFIG;
        
        $return = new stdClass();
        
        foreach($CONFIG as $key => $val)
            $return->$key = Library_tools::array_to_object($val);
        
        return $return;
    }
    
} // End Library_module