<?php
namespace Resources;

class Config {
    
    static public function main(){
        
        require APP . 'config/main.php';
        
        return $main;
    }
}