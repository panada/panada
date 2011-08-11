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
    
    static private $instance;
    
    public function __construct(){
        
        require APPLICATION . 'config.php';
        
        foreach($CONFIG as $key => $val)
            $this->$key = Library_tools::array_to_object($val);
    }
    
    public static function instance(){
        
        if( ! self::$instance ) {
            $cache = new Library_config();
            self::$instance = $cache;
            return $cache;
        }
        else {
            return self::$instance;
        }
    }
    
    /**
     * Get Base URL
     * 
     * @author  Aris S Ripandi
     * @since	Version 0.3.1
     *
     * @access public
     * @return void
     */
    public function base_url(){
        $base_url  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';	// protocol
        $base_url .= preg_replace('/:(80|443)$/', '', $_SERVER['HTTP_HOST']);							// host[:port]
        $base_url .= str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));							// path
        if (substr($base_url, -1) == '/') $base_url = substr($base_url, 0, -1);
        $base_url = $base_url . '/';
        return $base_url;
    }

} // End Library_config