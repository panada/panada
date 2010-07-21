<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada validation API.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */

class Library_validation {
    
    public function trim_lower($string){
        
        return trim(strtolower($string));
    }
    
    public function is_email($string) {
	
        $string = $this->trim_lower($string);
        return filter_var($string, FILTER_VALIDATE_EMAIL);
        
        /*
	$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
	
        if (strpos($string, '@') !== false && strpos($string, '.') !== false) {
	    
            if (preg_match($chars, $string))
		return $string;
	    else 
		return false;
	} 
	else {
            
	    return false;
	}
        */
    }
    
    public function is_url($string){
        
        $string = $this->trim_lower($string);
        return filter_var($string, FILTER_VALIDATE_URL);
        /*
        $chars = '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i';
        
        if( ! preg_match( $chars, $string ))
            return false;
        else
            return $string;
        */
    }
    
    public function strip_numeric($string){
        
        return filter_var($string, FILTER_SANITIZE_NUMBER_INT);
        //return preg_replace('/[^0-9]/', '', $string);
    }
    
    public function is_positive_numeric($string){
	
        return (bool) preg_match( '/^[0-9]*\.?[0-9]+$/', $string);
    }
    
    /**
     * EN: Use this for validate user first name and last name.
     * ID: Validasi nama baik itu nama depan ataupun belakang.
     */
    public function display_name($string){
        
        /*
         EN: Only permit a-z, 0-9 and .,'" space this is enough for a name right?
        */
        
        //'/[^a-zA-Z0-9s -_.,]/'
        $string = $this->trim_lower($string);
        $string = strip_tags($string);
        $string = stripslashes($string);
        $string = preg_replace( '/[^a-zA-Z0-9s .,"\']/', '', $string);
        
        return ucwords($string);
    }
}