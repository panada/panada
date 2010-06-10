<?php defined('THISPATH') or die('Tidak diperkenankan mengakses file secara langsung.');

/**
 * EN: Website base url.
 */
$CONFIG['base_url'] = 'http://localhost/panada/index.php/';

/**
 * EN: Database configuration.
 */
$CONFIG['db']['default']['host'] = ''; 
$CONFIG['db']['default']['user'] = ''; 
$CONFIG['db']['default']['password'] = ''; 
$CONFIG['db']['default']['database'] = '';

/**
 * ID: Defenisikan library apa saja yang akan diload secara otomatis.
 */
$CONFIG['auto_loader'] = array();

/**
 * EN: Session configuration.
 */

$CONFIG['session']['sesion_expire'] = 86400; //24 hour.
$CONFIG['session']['session_name'] = 'PAN_SID';
$CONFIG['session']['session_cookie_expire'] = 0;
$CONFIG['session']['session_cookie_path'] = '/';
$CONFIG['session']['session_cooke_secure'] = false;
$CONFIG['session']['session_cookie_domain'] = '';
$CONFIG['session']['session_in_db'] = false;
$CONFIG['session']['session_db_name'] = 'sessions';