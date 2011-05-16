<?php
/**
 * Panada
 *
 * Panada is a high performance PHP development framework, yet simple.
 * Not only in contexts about how to use it, but it also how the core system run.
 * Version: 0.3.1
 *
 * @package	Panada
 * @author	Iskandar Soesman
 * @copyright	Copyright (c) 2010, Iskandar Soesman.
 * @license	http://www.opensource.org/licenses/bsd-license.php
 * @link	http://panadaframework.com/
 * @since	Version 0.1
 */



/**
 * CONSTANTS
 * EN: Define the application root folder.
 * ID: Defenisikan applikasi root folder.
*/
define('THISPATH', dirname(__FILE__) . '/');



/**
 * EN: Panada main system folder location.
 * ID: Lokasi dimana folder panada berada.
 */
define('GEAR', 'panada' . '/');



/**
 * EN:	Application folder location.
 *	IF you have more then one application (multysite),
 *	copy this file (index.php) into your each folder application and
 *	adjust "THISPATH" and "APPLICATION" constants.
 *
 * ID:  Jika Anda memiliki beberapa website, copy file ini (index.php)
 *      ke dalam folder applikasi dan sesuaikan isi parameter untuk konstanta
 *      "THISPATH" dan "APPLICATION".
 */
define('APPLICATION', THISPATH . 'apps' .'/');



/**
 * ERROR REPORTING
 * ID:	Setting untuk menampilkan atau tidak menampilkan pesan error.
 *	Informasi lebih lanjut lihat di http://www.php.net/error_reporting
*/
error_reporting(E_ALL);



/**
 * ID: Nonaktifkan magic quotes jika masih aktif!
*/
if ( function_exists('get_magic_quotes_gpc') ) {
    
    function stripslashes_gpc(&$value){
        $value = stripslashes($value);
    }
    
    array_walk_recursive($_GET, 'stripslashes_gpc');
    array_walk_recursive($_POST, 'stripslashes_gpc');
    array_walk_recursive($_COOKIE, 'stripslashes_gpc');
    array_walk_recursive($_REQUEST, 'stripslashes_gpc');
}

/**
 * EN: Bootstrap file for Panada main system.
 * ID: Sistem utma Panada.
 */
require_once GEAR . 'gear.php';