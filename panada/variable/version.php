<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada version string
 *
 * @since	Version 0.3.1
 */

define('PANADA_VERSION', '0.3.1');


/**
 * Tentukan minimum versi PHP yang diperlukan oleh Panada.
 * Kompatibiltas sangatlah penting demi kelancaran kinerja.
 */

define('REQUIRED_PHP_VERSION', '5.2.4');


/**
 * (Opsional) Tentukan minimum versi database yang diperlukan
 */

define('REQUIRED_MYSQL_VERSION',  '5.1.0');
define('REQUIRED_MONGO_VERSION',  '0.0.0');
define('REQUIRED_SQLITE_VERSION', '0.0.0');



/**
 * EN: Check PHP version and compatibility
 * ID: Memeriksa kompatibilitas versi PHP
 */
 
function check_php_version() {
    $current_php_version  = phpversion();
    if ( version_compare( REQUIRED_PHP_VERSION, $current_php_version, '>' ) )
        exit('Panada '.PANADA_VERSION.' need PHP version '.REQUIRED_PHP_VERSION.' but your server is running PHP version '.$current_php_version.'.');
}