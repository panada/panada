<?php
namespace Controllers\Asal;
use Resources\Controllers;

class Testing extends Controllers {
    
    public function index(){
        
        echo __METHOD__;
    }
    
    public function driva(){
        
        print_r( func_get_args() );
    }
}