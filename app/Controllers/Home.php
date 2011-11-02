<?php
namespace Controllers;
use Resources\Controller;

class Home extends Controller {
    
    public function index(){
        
        $data['title'] = 'Hello world!';
        $data['mod'] = $this->model->sampleData->getData();
        
        $this->output('home', $data);
    }
}