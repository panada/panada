<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada HTML Generator.
 * EN: Create html tag programaticly.
 *
 * @package	    Panada
 * @subpackage	Library
 * @author	    Iskandar Soesman
 * @modify	    Aris S Ripandi
 * @since	    Version 0.1
 */

class Library_html {
    
    static private $doctypes    = array();
    static $break_line = "\r\n";
    
    static function doctype($type = 'xhtml1-strict', $echo = true) {
        global $doctypes;
        if (!is_array($doctypes)) {
            if (is_file(GEAR.'variable/doctypes.php')) {
                include GEAR.'variable/doctypes.php';
            } else {
                return FALSE;
            }
            if ( ! is_array(self::$doctypes)) {
                return FALSE;
            }
        }
        
        $str = $doctypes[$type] . self::$break_line;
        if($echo)
            echo $str;
        else
            return $str;
    }

    static function load_js($link, $echo = true){
        
        $str = '<script type="text/javascript" src="'.$link.'"></script>'. self::$break_line;
        if($echo)
            echo $str;
        else
            return $str;
    }
    
    static function load_css($link, $echo = true){
        
        $str = '<link rel="stylesheet" href="'.$link.'" type="text/css" media="screen" />'. self::$break_line;
        if($echo)
            echo $str;
        else
            return $str;
    }
    
    private function extract_properties($properties = array()){
        
        if ( ! empty($properties) ) {
            
            foreach($properties as $key => $val)
                $attr[] = ' '. $key . '="' . $val .'"';
            
            return implode('', $attr);
        }
        else {
            return null;
        }
    }
    
    static function href($properties = '', $echo = true){
        
        if( ! is_array($properties) ) {
            $text       = $properties;
            $properties = array('href' => $properties);
        }
        
        if( isset($properties['text']) ) {
            $text = $properties['text'];
            unset($properties['text']);
        }
        
        $properties = self::extract_properties($properties);
        $str        = '<a '.$properties.'>'.$text.'</a>';
        
        if($echo)
            echo $str;
        else
            return $str;
    }
    
    static function img($properties = '', $echo = true){
        
        if( ! is_array($properties) )
            $properties = array('src' => $properties);
        
        $properties = self::extract_properties($properties);
        
        $str = '<img '.$properties.' />';
        if($echo)
            echo $str;
        else
            return $str;
    }
    
    static function form_start($properties = '', $echo = true){
	
        if( ! is_array($properties) )
            $properties = array('method' => $properties, 'action' => '');
            
        $properties = self::extract_properties($properties);
        
        $str = '<form'.$properties.'>';
        
        if($echo)
            echo $str . self::$break_line;
        else
            return $str;
            
    }
    
    static function form_end($echo = true){
        
        $str = '</form>';
        
       if($echo)
            echo $str . self::$break_line;
        else
            return $str;
        
    }
    
    static function form_input($properties = array(), $echo = true){
	
        if( isset($properties['value']) )
            $properties['value'] = self::attribute_escape($properties['value']);
        
        $properties = self::extract_properties($properties);
        
        $str = '<input '.$properties.' />';
        
        if($echo)
            echo $str;
        else
            return $str;
            
    }
    
    static function form_select( $name, $values = array(), $echo = true){
        
        $str = '<select name="'.$name.'">' . self::$break_line;
        
        if( isset($values['default']) ) {
            $def_key = array_keys($values['default']);
            $str .= '<option value="'.$def_key.'">'.$values[$def_key].'</option>' . self::$break_line;
        }
        
        foreach($values['list'] as $key => $val) {
            
            $selected = '';
            
            if( isset($values['selected']) &&  $values['selected'] == $key) {
                $selected = ' selected="selected"';
            }
            
            $str .= '<option value="'.$key.'"'.$selected.'>'.$val.'</option>' . self::$break_line;
        }
        
         $str .= '</select>' . self::$break_line;
        
        if($echo)
            echo $str;
        else
            return $str;
    }
    
    static function form_textarea($properties = array(), $echo = true){
        
        if( isset($properties['value']) ) {
            $value = $properties['value'];
            unset($properties['value']);
        }
        else {
            $value = null;
        }
        
        $properties = self::extract_properties($properties);
        $str        = '<textarea'.$properties.'>'.$value.'</textarea>' . self::$break_line;
        
        if($echo)
            echo $str;
        else
            return $str;
    }
    
    static function attribute_escape($text){
        $safe_text = self::_specialchars($text, true);
        return $safe_text;
    }
    
    static function _specialchars( $text, $quotes = 0 ){
        
        $text = str_replace('&&', '&#038;&', $text);
        $text = str_replace('&&', '&#038;&', $text);
        $text = preg_replace('/&(?:$|([^#])(?![a-z1-4]{1,8};))/', '&#038;$1', $text);
        $text = str_replace('<', '&lt;', $text);
        $text = str_replace('>', '&gt;', $text);
        
        if ( 'double' === $quotes ) {
            $text = str_replace('"', '&quot;', $text);
        } elseif ( 'single' === $quotes ) {
            $text = str_replace("'", '&#039;', $text);
        } elseif ( $quotes ) {
            $text = str_replace('"', '&quot;', $text);
            $text = str_replace("'", '&#039;', $text);
        }
        
        return $text;
    }

} // End HTML creator class