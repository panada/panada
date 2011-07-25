<?php defined('THISPATH') or die('Can\'t access directly!');

class Controller_home extends Panada {
    
    public function __construct(){
        
        parent::__construct();
    }
    
    public function index(){
        
        $views = array(
            'page_title' => 'hello world!',
            'body' => 'This is hello world body!',
        );
        
        $this->view_index($views);
    }
}
