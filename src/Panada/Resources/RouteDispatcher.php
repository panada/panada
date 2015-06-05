<?php

namespace Panada\Resources;

class RouteDispatcher
{
    private $https = false;
    private $uri;
    private $route;
    private $mainConfig;

    public function __construct()
    {
        $this->mainConfig = Config::main();
        $this->readRoutes(APP . '/config/routes.php');
        $this->uri = $this->trimIgnoredPrefix($_SERVER['REQUEST_URI']);
        $this->route = Routes::getInstance()->parse($_SERVER['REQUEST_METHOD'], $this->uri);
    }

    public function controllerHandler()
    {
        $controller = 'Controllers\\' . $this->route['controller'];
        if (isset($this->route['module']) && (strlen($this->route['module']) > 0)) {
            $controller = 'Module\\' . $this->route['module'] . '\\' . $controller;
        }
        try {
            $instance = new $controller;
            return call_user_func_array(array($instance, $this->route['action']), [$this->route['args']]);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    private function readRoutes($routeFile)
    {
        if (file_exists($routeFile)) {
            include_once $routeFile;
        }
    }

    private function trimIgnoredPrefix($uri)
    {
        $this->prefix = '/';
        if (isset($this->mainConfig['PathPrefix'])) {
            $this->prefix = $this->mainConfig['PathPrefix'];
        }
        return preg_replace('|\A'.$this->prefix.'/|', '/', $uri);
    }
}
