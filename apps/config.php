<?php defined('THISPATH') or die('Can\'t access directly!');

/**
 * EN: Website base url.
 */
$CONFIG['base_url']                         = 'http://' .$_SERVER['HTTP_HOST'].'/'; /* Change this with your application domain and/or path. */
$CONFIG['index_file']                       = 'index.php/'; /* Remove this index.php if you wish an url without "index.php" */

/**
 * EN: Database configuration.
 */
$CONFIG['db']['default']['driver']          = 'mysql';
$CONFIG['db']['default']['host']            = ''; 
$CONFIG['db']['default']['user']            = ''; 
$CONFIG['db']['default']['password']        = ''; 
$CONFIG['db']['default']['database']        = '';
$CONFIG['db']['default']['charset']         = 'utf8';
$CONFIG['db']['default']['collate']         = 'utf8_general_ci';
$CONFIG['db']['default']['persistent']      = false;

/**
 * ID: Defenisikan library apa saja yang akan diload secara otomatis.
 */
$CONFIG['auto_loader'] = array();

/**
 * EN: Session configuration.
 */

$CONFIG['session']['expiration']        = 7200; /* 2 hour. */
$CONFIG['session']['name']              = 'PAN_SID';
$CONFIG['session']['cookie_expire']     = 0;
$CONFIG['session']['cookie_path']       = '/';
$CONFIG['session']['cookie_secure']     = false;
$CONFIG['session']['cookie_domain']     = '';
$CONFIG['session']['driver']            = 'native'; /* The option is 'native', 'cookie', cache or 'database' */
$CONFIG['session']['driver_connection'] = 'default'; /* Connection name for the driver. */
$CONFIG['session']['storage_name']      = 'sessions';

// SQL Table structure for session table
/*

CREATE TABLE sessions (
  session_id varchar(32) NOT NULL,
  session_data text NOT NULL,
  session_expiration int NOT NULL,
  PRIMARY KEY (session_id)
);
 */

/**
 * EN: Alias Controller configuration (eg: yoursite.com/username). Make sure the controller and the method are available.
 * ID: Konfigurasi Alias Controller (contoh: yoursite.com/username). Pastikan controller sudah tersedia, jika tidak akan menghasilkan error.
 *
 * array key => controller name
 * array value => controller's method
 */
$CONFIG['alias_controller'] = array();

/**
 * EN: Method name for 'hidden' method. Default is 'alias'. (eg: yoursite.com/CONTROLLER/username)
 * ID: Nama method untuk method 'tersembunyi'. Defaultnya adalah 'alias'. (contoh: yoursite.com/CONTROLLER/username)
 */
$CONFIG['alias_method'] = 'alias';

/**
 * EN:  GET Query filter type. See the option at http://www.php.net/manual/en/filter.filters.sanitize.php
 *      You can set the value to false, if you don't wanna filtering the query.
 */
$CONFIG['request_filter_type'] = FILTER_SANITIZE_STRING;

/**
 * EN:  Cache configuration
 */
$CONFIG['cache']['default']['driver']   = 'default'; /* The option is 'default', 'apc', 'memcache' or 'memcached' */

// Add this options for memcached/memcache
//$CONFIG['cache']['memcached']['driver'] = 'memcached';
//$CONFIG['cache']['memcached']['host'] = array('localhost');
//$CONFIG['cache']['memcached']['port'] = 11211;

$CONFIG['secret_key']                   = '_put_your_random_string_here_';