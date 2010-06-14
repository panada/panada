<?php defined('THISPATH') or die('Tidak diperkenankan mengakses file secara langsung.');

/**
 * EN: Website base url.
 */
$CONFIG['base_url']                         = 'http://localhost/panada/index.php/';

/**
 * EN: Database configuration.
 */
$CONFIG['db']['default']['host']            = ''; 
$CONFIG['db']['default']['user']            = ''; 
$CONFIG['db']['default']['password']        = ''; 
$CONFIG['db']['default']['database']        = '';

/**
 * ID: Defenisikan library apa saja yang akan diload secara otomatis.
 */
$CONFIG['auto_loader'] = array();

/**
 * EN: Session configuration.
 */

$CONFIG['session']['session_expire']        = 7200; /* 2 hour. */
$CONFIG['session']['session_name']          = 'PAN_SID';
$CONFIG['session']['session_cookie_expire'] = 0;
$CONFIG['session']['session_cookie_path']   = '/';
$CONFIG['session']['session_cookie_secure'] = false;
$CONFIG['session']['session_cookie_domain'] = '';
$CONFIG['session']['session_store']         = 'native'; /* The option is 'native' or 'db' */
$CONFIG['session']['session_db_name']       = 'sessions';

// MySQL Table structure for session table
/*

CREATE TABLE IF NOT EXISTS `sessions` (
  `session_id` varchar(32) NOT NULL,
  `session_data` text NOT NULL,
  `session_expiration` int(11) NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `session_expiration` (`session_expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

$CONFIG['short_url'] = array();