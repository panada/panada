<?php
/**
 * Panada validation API.
 *
 * @package	Resources
 * @link	http://panadaframework.com/
 * @license	http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 * @since	Version 0.1
 */
namespace Resources;

class Validation {
    
    public function trimLower($string){
        
        return trim(strtolower($string));
    }
    
    public function isEmail($string) {
	
	$string = $this->trimLower($string);
        
	$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
	
        if (strpos($string, '@') === false && strpos($string, '.') === false)
            return false;
	
        if ( ! preg_match($chars, $string))
            return false;
        
        return $string;
    }
    
    public function isUrl($string){
        
        $string = $this->trimLower($string);
        return filter_var($string, FILTER_VALIDATE_URL);
        /*
        $chars = '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i';
        
        if( ! preg_match( $chars, $string ))
            return false;
        else
            return $string;
        */
    }
    
    public function stripNumeric($string){
        
        return filter_var($string, FILTER_SANITIZE_NUMBER_INT);
        //return preg_replace('/[^0-9]/', '', $string);
    }
    
    public function isPositiveNumeric($string){
	
        return (bool) preg_match( '/^[0-9]*\.?[0-9]+$/', $string);
    }
    
    /**
     * EN: Use this for validate user first name and last name.
     * ID: Validasi nama baik itu nama depan ataupun belakang.
     */
    public function displayName($string){
        
        /*
         EN: Only permit a-z, 0-9 and .,'" space this is enough for a name right?
        */
        
        //'/[^a-zA-Z0-9s -_.,]/'
        $string = $this->trimLower($string);
        $string = strip_tags($string);
        $string = stripslashes($string);
        $string = preg_replace( '/[^a-zA-Z0-9s .,"\']/', '', $string);
        
        return ucwords($string);
    }
}