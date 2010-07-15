<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada Tools Class.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */

class Library_tools {
    
    function get_random_string($length = 12, $special_chars = true) {
        
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
	if ( $special_chars )
	    $chars .= '!@#$%^&*()';
        
	$str = '';
	for ( $i = 0; $i < $length; $i++ )
	    $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        
	return $str;
    }
    
} // End tools class