<?php defined('THISPATH') or die('Can\'t access directly!');

class Controller_home extends Panada {
    
    public function __construct(){
        
        parent::__construct();
    }
    
    public function index(){
        
        $users = new Model_users();
        $users->name = 'Budi';
        $users->email = 'budi@budi.com';
        $users->password = 'mypassword';
        var_dump($users->save());
    }
}
