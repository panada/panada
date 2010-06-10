<?php defined('THISPATH') or die('Tidak diperkenankan mengakses file secara langsung.');

class controller_welcome extends Panada {
    
    function __construct(){
        
        parent::__construct();
    }
    
    function index(){
        
        $views['page_title']    = 'hello world!';
        $views['body']          = 'This is hello world body!';
        $this->view('welcome_view', $views);
    }
}