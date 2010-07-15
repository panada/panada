<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada HTML Generator.
 * EN: Create html tag programaticly.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */

class Library_html {
    
    static function load_js($link, $echo = true){
        
        $str = '<script type="text/javascript" src="'.$link.'"></script>';
        if($echo)
            echo $str;
        else
            return $str;
    }
    
    static function load_css($link, $echo = true){
        
        $str = '<link rel="stylesheet" href="'.$link.'" type="text/css" media="screen" />';
        if($echo)
            echo $str;
        else
            return $str;
    }
    
    static function href($link, $properties = array(), $echo = true){
        
        $str = '<a href="'.$link.'">';
        if($echo)
            echo $str;
        else
            return $str;
    }
    
    static function img($link, $properties = array(), $echo = true){
        
        $str = '<img src="'.$link.'">';
        if($echo)
            echo $str;
        else
            return $str;
    }

} // End HTML creator class