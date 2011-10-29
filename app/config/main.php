<?php

$main = array(
    
    'module' => array(
        'path' => GEAR,
        'domainMapping' => array(),
    ),
    
    'vendor' => array(
        'path' => GEAR.'Vendors/'
    ),
    
    'alias' => array(
        /*
        'controller' => array(
            'class' => 'Controllers\Alias',
            'method' => 'index'
        ),
        */
        'method' => 'alias'
    ),
);