<?php

/**
 * Importer class.
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

class Import
{
    public static function vendor($filePath, $className = false, $arguments = array())
    {
        $config = Config::main();

        if (file_exists($file = $config['vendor']['path'].$filePath.'.php')) {
            include_once $file;
        }

        if (!$className) {
            $arr = explode('/', $filePath);
            $className = end($arr);
        } else {
            // Are we try to call static method?
            if (count(explode('::', $className)) > 1) {
                if (is_callable($className)) {
                    return call_user_func_array($className, $arguments);
                } else {
                    return constant($className);
                }
            }
        }

        $reflector = new \ReflectionClass($className);

        try {
            $object = $reflector->newInstanceArgs($arguments);
        } catch (\ReflectionException $e) {
            $object = new $className();
        }

        return $object;
    }

    /**
     * Method to embrace composer native autoloader.
     */
    public static function composer()
    {
        $config = Config::main();

        if (!$file = $config['vendor']['path'].'autoload.php') {
            throw new RunException('Composer autoloader not exists in your vendor folder '.$config['vendor']['path']);
        }

        require_once $file;
    }
}
