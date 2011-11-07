<?php
namespace Resources;

class Config {
    
    static private $config = array();
    
    static private function _cache($name){
        
        if( ! isset(self::$config[$name]) ) {
            require APP . 'config/'.$name.'.php';
            self::$config[$name] = $$name;
            return $$name;
        }
        else {
            return self::$config[$name];
        }
    }
    
    static public function main(){
        return self::_cache('main');
    }
    
    static public function session(){
        return self::_cache('session');
    }
    
    static public function cache(){
        return self::_cache('cache');
    }
    
    static public function database(){
        return self::_cache('database');
    }
}