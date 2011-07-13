<?php defined('THISPATH') or die('Can\'t access directly!');
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
        
        include $file_path;
        
        $class = ucwords($prefix).'_'.$file_name;
       
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
                'controller' => 'home'
            );
        }
        
        $default = array(
            'controller' => 'home'
        );
        
        $module = array_merge($default, $args);
        
        $file = 'module/' . $module['name'] . '/controller/' . $module['controller'];
        
        return new Library_loader($file);
    }
}

// End Library_loader