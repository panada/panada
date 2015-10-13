<?php

/**
 * Handler for configuration.
 *
 * @author  Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @link    http://panadaframework.com/
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 *
 * @since   version 1.0.0
 */
namespace Resources;

class Config
{
    private static $config = array();

    private static function _cache($name)
    {
        if (!isset(self::$config[$name])) {
            $array = require APP.'config/'.$name.'.php';
            self::$config[$name] = $array;

            return $array;
        } else {
            return self::$config[$name];
        }
    }

    public static function main()
    {
        return self::_cache('main');
    }

    public static function session()
    {
        return self::_cache('session');
    }

    public static function cache()
    {
        return self::_cache('cache');
    }

    public static function database()
    {
        return self::_cache('database');
    }

    /**
     * Handler for user defined config.
     */
    public static function __callStatic($name, $arguments = array())
    {
        // Does cache for this config exists?
        if (isset(self::$config[$name])) {
            return self::$config[$name];
        }

        // Does the config file exists?
        try {
            if (!file_exists($file = APP.'config/'.$name.'.php')) {
                throw new RunException('Config file in '.$file.' does not exits');
            }
        } catch (RunException $e) {
            RunException::outputError($e->getMessage());
        }

        return self::_cache($name);
    }
}
