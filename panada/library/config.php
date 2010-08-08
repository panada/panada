<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada Config hendler.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */

class Library_config {
    
    public function __construct(){
        
        require APPLICATION . 'config.php';
        
        foreach($CONFIG as $key => $val)
            $this->$key = $this->assign_object($val);
    }
    
    private function assign_object($var) {
        
        if( is_array($var) ) {
            
            $object = new stdClass();
            foreach($var as $key => $val)
                $object->$key = $this->assign_object($val);
            
            return $object;
        }
        
        return $var;
    }
} // End Library_config