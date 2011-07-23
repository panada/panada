<?php defined('THISPATH') or die('Can\'t access directly!');

$CONFIG = array(
    
    /**
     * Allow all controller can be accessed via URL.
     * Set to "false" if don't.
     * Defined as array if you only permit some, for example: 'allow_url_routing' => array('home', 'login', 'logout')
     * if you only permit home, login and logout controller.
     */
    'allow_url_routing' => true,
    
    /**
     * Define the 'controller_name' => 'method_name' if you
     * want implement alias controller feature in this module.
     */
    'alias_controller' => array(),
    
);