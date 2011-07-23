<?php defined('THISPATH') or die('Can\'t access directly!');

class Mod_sample_controller_home extends Panada_module {
    
    public function __construct(){
        
        parent::__construct();
        
        $this->test     = new Mod_sample_library_test;
        $this->dummy    = new Mod_sample_model_dummy;
        $this->request  = new Library_request;
    }
    
    public function index(){
        
        $this->output(
            'index',
            array(
                'module_name' => self::$_module_name,
                'library_string' => $this->test->str(),
                'model_string' => $this->dummy->str(),
                'main_lib' => $this->request->get('name')
            )
        );
    }
}