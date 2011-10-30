<?php
namespace Modules\Blog\Controllers;
use Resources\Controller;
use Modules\Blog\Models as Modules;

class Home extends Controller {
    
    public function index(){
        
        echo __METHOD__;
    }
    
    public function mika(){
        
        $data = new Modules\Data;
        print_r( $data->name() );
    }
}