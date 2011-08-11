<?php
/**
 * Panada
 *
 * Panada is a high performance PHP development framework, yet simple.
 * Not only in contexts about how to use it, but it also how the core system run it.
 * Version: 0.3.1
 *
 * @package	    Panada
 * @author	    Iskandar Soesman
 * @modify	    Aris S Ripandi
 * @copyright	Copyright (c) 2010, Iskandar Soesman.
 * @license	    http://www.opensource.org/licenses/bsd-license.php
 * @link	    http://panadaframework.com/
 * @since	    Version 0.1
 */

/**
 * --------------------------------------------------------------------
 * ENVIRONMENT & ERROR REPORTING
 * --------------------------------------------------------------------
 * Sesuaikan nilai parameter folder sistem dan aplikasi
 *
 * Path parameter:
 *    sys_folder    : folder tempat sistem panada berada
 *    app_folder    : folder tempat aplikasi diletakan
 *
 * The value is:
 *    production    : hide error message
 *    development   : show all error message for debugging
 * --------------------------------------------------------------------
 */
	$app_folder     = 'apps';
	$sys_folder     = 'panada';
	$environment    = 'development';
    

// --------------------------------------------------------------------
// END OF USER CONFIGURABLE SETTINGS. DO NOT EDIT BELOW THIS LINE
// --------------------------------------------------------------------

    // error reporting
    define('ENVIRONMENT', $environment);
    if (defined('ENVIRONMENT')) {
        switch (ENVIRONMENT) {
            case 'development':
                error_reporting(E_ALL);
            break;
            case 'production':
                error_reporting(0);
            break;
            default:
                exit('The application environment is not set correctly.');
        }
    }
    
	// Is the system path correct?
	if (!is_dir($sys_folder)) {
		exit("Your system folder path does not appear to be set correctly. Please open the following file and correct this: ".pathinfo(__FILE__, PATHINFO_BASENAME));
	}
	

	// Is the application path correct?
	if (!is_dir($app_folder)) {
		exit("Your application folder path does not appear to be set correctly. Please open the following file and correct this: ".pathinfo(__FILE__, PATHINFO_BASENAME));
	}
    
	// Definiskan path folder system dan aplikasi
	define('THISPATH', realpath('.') . '/');
	define('GEAR', THISPATH . $sys_folder . '/');
	define('APPLICATION', THISPATH . $app_folder . '/');
    
    // Nonaktifkan magic quotes jika masih aktif
    if ( function_exists('get_magic_quotes_gpc') ) {
        function stripslashes_gpc(&$value){
            $value = stripslashes($value);
        }
        array_walk_recursive($_GET, 'stripslashes_gpc');
        array_walk_recursive($_POST, 'stripslashes_gpc');
        array_walk_recursive($_COOKIE, 'stripslashes_gpc');
        array_walk_recursive($_REQUEST, 'stripslashes_gpc');
    }
    
    // cek versi php di server
    require_once GEAR. 'variable/version.php';
    check_php_version();
    
    // Load bootstrap
    require_once GEAR . 'gear.php';
    