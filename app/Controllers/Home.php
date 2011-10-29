<?php
namespace Controllers;
use Resources\Controller;

class Home extends Controller {
    
    public function index(){
        
        $data['mod'] = $this->model->sampleData->getData();
        
        $this->output('home', $data);
    }
    
    public function mika(){
        
        echo 'mika';
    }
    
    public function alias(){
        
        print_r( func_get_args() );
    }
}