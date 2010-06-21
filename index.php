<?php
/**
 * Panada
 *
 * Light and simple PHP 5 base Framework.
 *
 * @package	Panada
 * @author	Iskandar Soesman
 * @copyright	Copyright (c) 2010, Iskandar Soesman.
 * @license	http://www.opensource.org/licenses/bsd-license.php
 * @link	http://www.kandar.info/panada/
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
define('APPLICATION', THISPATH . 'application' .'/');



/**
 * ERROR REPORTING
 * ID:	Setting untuk menampilkan atau tidak menampilkan pesan error.
 *	Informasi lebih lanjut lihat di http://www.php.net/error_reporting
*/
error_reporting(E_ALL);



/**
 * ID: Nonaktifkan magic quotes jika masih aktif!
*/
@set_magic_quotes_runtime(0);

/**
 * EN: Bootstrap file for Panada main system.
 * ID: Sistem utma Panada.
 */
require_once THISPATH . GEAR . 'system.php';