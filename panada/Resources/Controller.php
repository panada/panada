<?php

/**
 * Handler for controller process.
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

class Controller
{
    private $childNamespace,
        $viewCache,
        $viewFile,
        $configMain;

    public $config = [];

    public function __construct()
    {
        $child = get_class($this);

        $this->childClass = [
                            'namespaceArray' => explode('\\', $child),
                            'namespaceString' => $child,
                        ];

        $this->configMain   = Config::main();
        $this->uri          = \Gear::$uri;
    }

    public function __get($class)
    {
        $classNamespace = [
            'model' => 'Models',
            'Model' => 'Models',
            'models' => 'Models',
            'Models' => 'Models',
            'library' => 'Libraries',
            'Library' => 'Libraries',
            'libraries' => 'Libraries',
            'Libraries' => 'Libraries',
            'Resources' => 'Resources',
            'resources' => 'Resources',
            'Resource' => 'Resources',
            'resource' => 'Resources',
        ];

        try {
            if (!isset($classNamespace[$class])) {
                throw new \Exception('Undefined property '.$class);
            }
        } catch (\Exception $e) {
            $arr = $e->getTrace();
            RunException::outputError($e->getMessage(), $arr[0]['file'], $arr[0]['line']);
        }

        return new PropertiesLoader($this->childClass['namespaceArray'], $classNamespace[$class]);
    }

    public static function outputError($file, $data = [])
    {
        return (new self)->output($file, $data);
    }

    public function output($panadaViewfile, $data = [])
    {
        $panadaFilePath = APP.'views/'.$panadaViewfile;

        if ($this->childClass['namespaceArray'][0] == 'Modules') {
            $panadaFilePath = $this->configMain['module']['path'].$this->childClass['namespaceArray'][0].'/'.$this->childClass['namespaceArray'][1].'/views/'.$panadaViewfile;
        }

        try {
            if (!file_exists($this->viewFile = $panadaFilePath.'.php')) {
                throw new RunException('View file in '.$this->viewFile.' does not exits');
            }
        } catch (RunException $e) {
            $arr = $e->getTrace();
            RunException::outputError($e->getMessage(), $arr[0]['file'], $arr[0]['line']);
        }

        if (!empty($data)) {
            $this->viewCache = array(
                'data' => $data,
                'prefix' => $this->childClass['namespaceString'],
            );
        }

        // We don't need this variables anymore.
        unset($panadaViewFile, $data, $panadaFilePath);

        if (!empty($this->viewCache) && $this->viewCache['prefix'] == $this->childClass['namespaceString']) {
            extract($this->viewCache['data'], EXTR_SKIP);
        }

        ob_start();
        include $this->viewFile;
        $return = ob_get_contents();
        ob_end_clean();

        return $return;
    }

    public function outputJSON($data)
    {
        return $this->outputTransporter($data, 'json');
    }

    public function outputXML($data)
    {
        return $this->outputTransporter($data, 'xml');
    }

    private function outputTransporter($data, $type)
    {
        return (new Rest)->wrapResponseOutput($data, $type);
    }

    public function location($location = '')
    {
        return $this->uri->getBaseUri().$this->configMain['indexFile'].$location;
    }

    public function redirect($location = '', $status = 302)
    {
        if (substr($location, 0, 4) != 'http') {
            $location = $this->location().$location;
        }

        Response::setHeader('Location', $location, $status);

        return '<html><head><meta http-equiv="refresh" content="0; url='.$location.'" /></head></html>';
    }
}
