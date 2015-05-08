<?php
namespace Controllers;

use Panada\Resources;

class Home extends Resources\Controller
{
    public function index()
    {
        $data['title'] = 'Hello world!';

        return $this->output('home', $data);
    }
}
