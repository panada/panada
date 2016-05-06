<?php

namespace Tests\Resources;

class Encyption extends \PHPUnit_Framework_TestCase
{
    public $connection = 'default';

    public function setUp()
    {
        new \Test\Bootstrap;
    }

    public function testEncrypt()
    {
        $this->assertTrue(true);
    }
}
