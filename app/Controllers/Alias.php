<?php
namespace Controllers;
use Resources\Controllers;

class Alias extends Controllers {
    
    public function index(){
        
        print_r( func_get_args() );
    }
}