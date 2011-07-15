<?php defined('THISPATH') or die('Can\'t access directly!');

/**
 * Experiment for Module hendler
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
        
        $file_name = explode('_', strtolower($class) );
        
        $file = GEAR . 'module/'. self::$_module_name . '/' . $file_name[0] . '/' . $file_name[1] .'.php';
        
        include_once $file;
    }
}
//end module hendler


/**
 * Panada Class Magic loader.
 *
 * Yang perlu dilakukan berikutnya adalah cache file yg sudah pernah dipanggil
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.3
 */

class Library_loader {
    
    private $class_inctance;
    
    /**
     * Class constructor
     *
     * @param string File path location
     * @return void
     */
    public function __construct($file_path){
        
        $arr = explode('/', $file_path);
        $file_name = end( $arr );
        
        // Are we try to load a module?
        if($arr[0] == 'module' ){
            $prefix = $arr[2];
            $file_path = GEAR . $file_path.'.php';
        }
        else{
            $file_path = APPLICATION . $file_path.'.php';
            $prefix = $arr[0];
        }
        
        if( ! file_exists( $file_path ) )
            Library_error::_500('<b>Error:</b> No <b>'.$file_name.'</b> file in '.$arr[0].' folder.');
        
        $class = ucwords($prefix).'_'.$file_name;
        
        include_once $file_path;
        
        if( ! class_exists($class) )
            Library_error::_500('<b>Error:</b> No class <b>'.$class.'</b> exists in file '.$file_name.'.');
        
        $this->class_inctance = new $class;
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
     * Loader for module
     *
     * @param mix String or array for module configuration
     * @return object
     */
    public static function module($args){
        
        if( is_string($args) ){
            
            $args = array(
                'name' => $args,
                'controller' => $args
            );
        }
        
        $default = array(
            'controller' => 'home'
        );
        
        $module = array_merge($default, $args);
        
        $file = 'module/' . $module['name'] . '/controller/' . $module['controller'];
        
        Panada_module::$_module_name = $module['name'];
        
        $module_controller = new Library_loader($file);
        
        return $module_controller;
    }
}

// End Library_loader