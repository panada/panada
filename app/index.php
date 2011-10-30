<?php

// You can adjust this following constants if necessary.

// The APP constants is where your application folder located.
define('APP', dirname(__FILE__) . '/');

// The INDEX_FILE constants is this defailt file name.
define('INDEX_FILE', basename(__FILE__));

// And the GEAR constants is where panada folder located.
define('GEAR', '../panada/');

require_once GEAR.'Gear.php';

new Gear;