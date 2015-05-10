<?php
/**
 * Bootstraping all requirement configuration
 *
 */
class Bootstrap
{
    public function __construct()
    {
        // check this CONST already defened since Composer use require instead of require_once.
        if (!defined('APP')) {
            define('APP', dirname(__FILE__) . '/app/');
        }

    }
}

new Bootstrap;
