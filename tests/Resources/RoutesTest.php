<?php

namespace Tests\Resources;

use Resources\Routes;

class RoutesTest extends \PHPUnit_Framework_TestCase
{
    public function testGetStatic()
    {
        Routes::get('/test1', ['controller' => 'TestController', 'action' => 'testMethod']);

        $route = Routes::getInstance()->parse('GET', '/test1');
        $this->assertEquals('TestController', $route['controller']);
        $this->assertEquals('testMethod',     $route['action']);
        $this->assertEquals(['GET'],          $route['methods']);
    }

    public function testGetDynamic()
    {
        Routes::get('/test1/:id', ['controller' => 'TestController1', 'action' => 'testMethod1']);

        $route = Routes::getInstance()->parse('GET', '/test1/234');
        $this->assertEquals('TestController1', $route['controller']);
        $this->assertEquals('testMethod1',     $route['action']);
        $this->assertEquals(['id' => '234'],   $route['args']);
    }

    public function testGetDynamic1()
    {
        Routes::post('/test_dyn/:name/:action',
                     ['controller' => 'TestDynController', 'action' => 'dynMethod']);

        $route = Routes::getInstance()->parse('POST', '/test_dyn/abc/def');
        $this->assertEquals('TestDynController',                  $route['controller']);
        $this->assertEquals('dynMethod',                          $route['action']);
        $this->assertEquals(['name' => 'abc', 'action' => 'def'], $route['args']);
    }

    public function testUndefinedRoute()
    {
        $route = Routes::getInstance()->parse('PUT', '/test1');
        $this->assertEquals(null, $route);
    }

    public function testRouteWithModule()
    {
        Routes::get('/News/:news_id/Articles/:article_id', [
            'module' =>     'Entry',
            'controller' => 'NewsArticle',
            'action' =>     'show'
        ]);

        $route = Routes::getInstance()->parse('GET', '/News/technology/Articles/123');
        $this->assertEquals('Entry',       $route['module']);
        $this->assertEquals('NewsArticle', $route['controller']);
        $this->assertEquals('show',        $route['action']);
        $this->assertEquals(['news_id' => 'technology', 'article_id' => '123'], $route['args']);
    }
}
