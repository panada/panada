<?php defined('THISPATH') or die('Can\'t access directly!');

class Controller_home extends Panada {
    
    public function __construct(){
        
        parent::__construct();
    }
    
    public function index(){
        
        $views['page_title']    = 'hello world!';
        $views['body']          = 'This is hello world body!';
        $this->view('index', $views);
    }
}