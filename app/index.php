<?php
define('APP', dirname(__FILE__) . '/');
define('INDEX_FILE', basename(__FILE__));
define('GEAR', '../panada/');

require_once GEAR.'Gear.php';

spl_autoload_register('Gear::loader');
Gear::init();