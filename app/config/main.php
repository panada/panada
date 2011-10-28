<?php

$main = array(
    
    'module' => array(
        'path' => GEAR.'Modules/',
        'domainMapping' => array(),
    ),
    
    'vendor' => array(
        'path' => GEAR.'Vendors/'
    ),
    
    'alias' => array(
        'controller' => array('Controllers\Alias', 'index'),
        'method' => 'alias'
    ),
);