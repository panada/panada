<?php

return [

    'default' => [
        'driver' => 'dummy',
    ],

    'apc' => [
        'driver' => 'apc',
    ],

    'memcached' => [
        'driver' => 'memcache',
        'server' => [
            [
                'host' => 'localhost',
                'port' => 11211,
                'persistent' => false,
            ],
       ]
    ],

    'memcache' => [
        'driver' => 'memcache',
        'server' => [
            [
                'host' => 'localhost',
                'port' => 11211,
                'persistent' => false,
            ],
       ]
    ],

    'redis' => [
        'driver' => 'redis',
        'server' => [
            [
                'host' => 'localhost',
                'port' => 6379,
                'persistent' => false,
                'timeout' => 2.5
            ],
       ]
    ],
];
