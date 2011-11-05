<?php

// You can adjust this following constants if necessary.

// The APP constant is where your application folder located.
define('APP', dirname(__FILE__) . '/');

// The INDEX_FILE constant is this defailt file name.
define('INDEX_FILE', basename(__FILE__));

// And the GEAR constant is where panada folder located.
define('GEAR', '../panada/');

require_once GEAR.'Gear.php';

// http://www.php.net/error_reporting
new Gear;