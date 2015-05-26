<?php

use \Resources\Routes;

// This is only served as examples to show how Routes being used
// in an application

Routes::get('/', ['controller' => 'Home', 'action' => 'index']);

Routes::get('/name', [
    'module' =>     'Backend',
    'controller' => 'User',
    'action' =>     'show'
]);
