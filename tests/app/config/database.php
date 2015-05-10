<?php

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
    
    /*

    'sqlite' => array(
        'driver' => 'sqlite',
        'host' => '',
        'user' => '',
        'password' => '',
        'database' => '/path/to/sqlitedb/my.db',
        'charset' => '',
        'collate' => '',
        'persistent' => false,
    ),

    'mongodb' => array(
        'driver' => 'mongodb',
        'host' => 'localhost',
        'port' => 27017,
        'user' => '',
        'password' => '',
        'database' => '',
        'tablePrefix' => '',
        'charset' => '',
        'collate' => '',
        'persistent' => false,
        'options' => array(),
    ),

    'cubrid' => array(
        'driver' => 'cubrid',
        'host' => 'localhost',
        'user' => 'root',
        'port' => 33000,
        'password' => '',
        'database' => 'panada',
        'tablePrefix' => '',
        'charset' => 'utf8',
        'collate' => 'utf8_general_ci',
        'persistent' => false,
        'autoCommit' => true,
    ),
    */

);
