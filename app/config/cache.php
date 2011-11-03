<?php

$cache = array(
    
    'default' => array(
        'driver' => 'dummy',
    ),
    
    'apc' => array(
        'driver' => 'apc',
    ),
    
    'memcached' => array(
        'driver' => 'memcached',
        'host' => array(
            'localhost'
        ),
        'port' => 11211,
    ),
    
    'memcache' => array(
        'driver' => 'memcache',
        'host' => array(
            'localhost'
        ),
        'port' => 11211,
    ),
);