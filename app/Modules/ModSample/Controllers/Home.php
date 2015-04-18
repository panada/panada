<?php
namespace Modules\ModSample\Controllers;
use Resources;

class Home extends Resources\Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
		$data['title'] = 'Hello world from "ModSample" module !<br/><br/>view path : /Panada/app/Modules/ModSample/Views/home.php';
       
		// Load view sample, from this module view file
		$this->output('home', $data);
    }
	
	public function default_view_folder()
	{
		$data['title'] = 'Hello world from "ModSample" module !<br/><br/>view path : /Panada/app/views/home_mod_sample.php';
		
		// Load view sample, from default view file
		$this->output('home_mod_sample', $data, false, true);
	}
}
