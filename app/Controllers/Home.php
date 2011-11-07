<?php
namespace Controllers;
use Resources, Models;

class Home extends Resources\Controller {
    
    public function index(){
        
        $data['title'] = 'Hello world!';
        $data['mod'] = $this->model->sampleData->getData();
        
        $this->output('home', $data);
    }
}