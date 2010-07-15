<?php defined('THISPATH') or die('Can\'t access directly!');

class controller_home extends Panada {
    
    function __construct(){
        
        parent::__construct();
    }
    
    function index(){
        
        $views['page_title']    = 'hello world!';
        $views['body']          = 'This is hello world body!';
        $this->view('index', $views);
    }
}