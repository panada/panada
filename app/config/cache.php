<?php

return array(
    
    'default' => array(
        'driver' => 'dummy',
    ),
    
    'apc' => array(
        'driver' => 'apc',
    ),
    
    'memcached' => array(
        'driver' => 'memcached',
        'server' => array(
            array(
                'host' => 'localhost',
                'port' => 11211,
                'persistent' => false,
            ),
        )
    ),
    
    'memcache' => array(
        'driver' => 'memcache',
        'server' => array(
            array(
                'host' => 'localhost',
                'port' => 11211,
                'persistent' => false,
            ),
        )
    ),
);