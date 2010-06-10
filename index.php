<?php
/**
 * Panada
 *
 * Light and simple PHP 5 base Framework.
 *
 * @package	Panada
 * @author	Kandar
 * @copyright	Copyright (c) 2010, Iskandar Soesman.
 * @license	http://www.opensource.org/licenses/bsd-license.php
 * @link	http://www.kandar.info/panada/
 * @since	Version 0.1
 * @filesource
 */

// -------------------------------------------------------------------



/**
 * CONSTANTS
 * EN: Defined the application root folder.
 * ID: Defenisikan applikasi root folder.
*/
define('THISPATH', dirname(__FILE__) . '/');



//EN: Panada main system folder location.
define('GEAR', 'panada' . '/');



/**
 * EN:	Application folder location.
 *	IF you have more then one application (multysite),
 *	copy this file (index.php) into your folder application and
 *	adjust "THISPATH" and "APPLICATION" constants.
 */
define('APPLICATION', THISPATH . 'application' .'/');



/**
 * ERROR REPORTING
 * ID:	Setting untuk menampilkan atau tidak menampilkan pesan error.
 *	Informasi lebih lanjut lihat di http://www.php.net/error_reporting
*/
error_reporting(E_ALL);



/**
 * Nonaktifkan magic quotes jika masih aktif!
*/
/*
if (get_magic_quotes_gpc()) {
    
    function stripslashes_gpc(&$value){
        $value = stripslashes($value);
    }
    
    array_walk_recursive($_GET, 'stripslashes_gpc');
    array_walk_recursive($_POST, 'stripslashes_gpc');
    array_walk_recursive($_COOKIE, 'stripslashes_gpc');
    array_walk_recursive($_REQUEST, 'stripslashes_gpc');
}
*/
@set_magic_quotes_runtime(0);

//EN: Bootstrap file for Panada main system.
require_once THISPATH . GEAR . 'system.php';