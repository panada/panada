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
    
    public function __construct($file_path){
        
        $arr = explode('/', $file_path);
        $file_name = end( $arr );
        
        if( ! file_exists( $file_path = APPLICATION . $file_path.'.php' ) )
            Library_error::_500('<b>Error:</b> No <b>'.$file_name.'</b> file in '.$arr[0].' folder.');
        
        include $file_path;
        
        $class = ucwords($arr[0]).'_'.$file_name;
        
        if( ! class_exists($class) )
            Library_error::_500('<b>Error:</b> No class <b>'.$class.'</b> exists in file '.$file_name.'.');
        
        $this->class_inctance = new $class;
    }
    
    public function __call($name, $arguments = array() ){
        
        return call_user_func_array( array($this->class_inctance, $name), $arguments );
    }
    
    public function __get($name){
        
        return $this->class_inctance->$name;
    }
    
    public function __set($name, $value){
        
        $this->class_inctance->$name = $value;
    }
}

// End Library_loader