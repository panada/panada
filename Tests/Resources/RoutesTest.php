<?php

namespace Tests\Resources;

use Resources\Routes;

class RoutesTest extends \PHPUnit_Framework_TestCase
{
    public function testGetStatic()
    {
        Routes::get('/test1', ['controller' => 'TestController', 'action' => 'testMethod']);

        $route = Routes::getInstance()->parse('GET', '/test1');
        $this->assertEquals($route['controller'], 'TestController');
        $this->assertEquals($route['action'], 'testMethod');
        $this->assertEquals($route['methods'], ['GET']);
    }

    public function testGetDynamic()
    {
        Routes::get('/test1/:id', ['controller' => 'TestController1', 'action' => 'testMethod1']);

        $route = Routes::getInstance()->parse('GET', '/test1/234');
        $this->assertEquals($route['controller'], 'TestController1');
        $this->assertEquals($route['args'], ['id' => '234']);
    }

    public function testUndefinedRoute()
    {
        $route = Routes::getInstance()->parse('PUT', '/test1');
        $this->assertEquals($route, null);
    }
}
