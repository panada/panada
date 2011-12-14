<?php

return array(
    
    // Just put null value if you has enable .htaccess file
    'indexFile' => INDEX_FILE . '/',
    
    'module' => array(
        'path' => GEAR,
        'domainMapping' => array(),
    ),
    
    'vendor' => array(
        'path' => GEAR.'vendors/'
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