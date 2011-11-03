<?php
namespace Resources;

class Config {
    
    static private $config = array();
    
    static public function main(){
        
        if( ! isset(self::$config['main']) ) {
            require APP . 'config/main.php';
            self::$config['main'] = $main;
            return $main;
        }
        else {
            return self::$config['main'];
        }
    }
}