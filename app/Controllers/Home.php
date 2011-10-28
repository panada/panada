<?php
namespace Controllers;
use Resources\Controllers;

class Home extends Controllers {
    
    public function index(){
        
        $data['mod'] = $this->models->sampleData->getData();
        
        $this->output('home', $data);
    }
    
    public function mika(){
        
        echo 'mika';
    }
    
    public function alias(){
        
        print_r( func_get_args() );
    }
}