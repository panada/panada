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
        
        $file_path = APPLICATION . $file_path.'.php';
        $prefix = $arr[0];
       
        
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
}

// End Library_loader