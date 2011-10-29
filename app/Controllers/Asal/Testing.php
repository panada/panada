<?php
namespace Controllers\Asal;
use Resources\Controller;

class Testing extends Controller {
    
    public function index(){
        
        echo __METHOD__;
    }
    
    public function driva(){
        
        print_r( func_get_args() );
    }
}