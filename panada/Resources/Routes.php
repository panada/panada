<?php

namespace Resources;

/**
 * Routes enable more flexible URL since user may define their 
 * own URL to `Controller::action` mapping.
 */
class Routes
{
    private $urlStaticMap = [
        'GET' =>    [],
        'POST' =>   [],
        'PUT' =>    [],
        'PATCH' =>  [],
        'DELETE' => [],
    ];

    private $urlPatternsMap = [
        'GET' =>    [],
        'POST' =>   [],
        'PUT' =>    [],
        'PATCH' =>  [],
        'DELETE' => [],
    ];

    private static $instance;
    
    // /books/:id/:title
    const PARAMRGX = "/\:([^\s\/]+)/";

    private function __construct()
    {
    }

    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public static function map($urlPattern, $options = [], $subrouter = null)
    {
        self::get_instance()->addMap($urlPattern, $options, $subrouter);
    }

    public static function get($urlPattern, $options = [], $subrouter = null)
    {
        $options['methods'] = ['GET'];
        self::get_instance()->addMap($urlPattern, $options, $subrouter);
    }

    public static function post($urlPattern, $options = [], $subrouter = null)
    {
        $options['methods'] = ['POST'];
        self::get_instance()->addMap($urlPattern, $options, $subrouter);
    }

    public static function put($urlPattern, $options = [], $subrouter = null)
    {
        $options['methods'] = ['PUT'];
        self::get_instance()->addMap($urlPattern, $options, $subrouter);
    }

    public static function patch($urlPattern, $options = [], $subrouter = null)
    {
        $options['methods'] = ['PATCH'];
        self::get_instance()->addMap($urlPattern, $options, $subrouter);
    }

    public function parse($method, $request_uri)
    {
        if (!array_key_exists($method, $this->urlStaticMap) || $method === 'HEAD') {
            $method = 'GET';
        }

        if (array_key_exists($request_uri, $this->urlStaticMap[$method])) {
            return $this->urlStaticMap[$method][$request_uri];
        }

        foreach ($this->urlPatternsMap[$method] as $route) {
            preg_match_all($route['matcher'], $request_uri, $args);
            if (count($args[0]) === count($route['params'])) {
                $route['args'] = array_combine($route['params'], $args[1]);
                return $route;
            }
        }
        return null;
    }

    public function addMap($urlPattern, $options = [], $subrouter = null)
    {
        preg_match_all(self::PARAMRGX, $urlPattern, $params);
        if (count($params) && count($params[0])) {
            $matcher = $urlPattern;
            foreach ($params[0] as $param) {
                $matcher = '|\A'.str_replace("$param", "([^/]+)", $matcher).'\z|';
            }
            $options['matcher'] = $matcher;
            $options['params'] = $params[1];
            foreach($options['methods'] as $method) {
                $this->urlPatternsMap[$method][$urlPattern] = $options;
            }
            return;
        }
        foreach ($options['methods'] as $method) {
            $options['args'] = [];
            $this->urlStaticMap[$method][$urlPattern] = $options;
        }
    }

}
