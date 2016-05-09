<?php
if(getenv('mode') == 'ci'){
    return array(

        'default' => array(
            'driver' => 'mysqli',
            'host' => '127.0.0.1',
            'port' => 3306,
            'user' => 'root',
            'password' => 'semaphoredb',
            'database' => 'panada',
            'tablePrefix' => '',
            'charset' => 'utf8',
            'collate' => 'utf8_general_ci',
            'persistent' => false,
        ),

        'pqsql' => array(
            'driver' => 'pgsql',
            'host' => 'localhost',
            'port' => 5432,
            'user' => 'runner',
            'password' => 'semaphoredb',
            'database' => 'panada',
            'tablePrefix' => '',
            'charset' => 'utf8',
            'collate' => 'utf8_general_ci',
            'persistent' => false,
        ),
    );
}
else {
    return array(

        'default' => array(
            'driver' => 'mysqli',
            'host' => '127.0.0.1',
            'port' => 3306,
            'user' => 'root',
            'password' => '',
            'database' => 'panada',
            'tablePrefix' => '',
            'charset' => 'utf8',
            'collate' => 'utf8_general_ci',
            'persistent' => false,
        ),

        'pqsql' => array(
            'driver' => 'pgsql',
            'host' => 'localhost',
            'port' => 5432,
            'user' => 'kandar',
            'password' => 'kandar',
            'database' => 'panada',
            'tablePrefix' => '',
            'charset' => 'utf8',
            'collate' => 'utf8_general_ci',
            'persistent' => false,
        ),
    );
}
