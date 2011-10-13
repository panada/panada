<?php
namespace Controllers;
use Resources\Controllers;

class Home extends Controllers {
    
    public function index(){
        
        $data['mod'] = $this->models->sampleData->getData();
        
        $this->output('home', $data);
    }
}