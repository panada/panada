<?php
namespace Controllers;
use Resources\Controller;

class Alias extends Controller {
    
    public function index(){
        
        print_r( func_get_args() );
    }
}