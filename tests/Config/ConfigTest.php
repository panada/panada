<?php
namespace Tests\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        new \Tests\Bootstrap;
    }
    
    public function testConfigMainArray()
    {
        $this->assertTrue( is_array(\Resources\Config::main()) );
    }
    
    public function testConfigCacheArray()
    {
        $this->assertTrue( is_array(\Resources\Config::cache()) );
    }
    
    public function testConfigDatabaseArray()
    {
        $this->assertTrue( is_array(\Resources\Config::database()) );
    }
    
    public function testConfigSecurityArray()
    {
        $this->assertTrue( is_array(\Resources\Config::security()) );
    }
    
    public function testConfigSessionArray()
    {
        $this->assertTrue( is_array(\Resources\Config::session()) );
    }
    
    public function testConfigCostumeArray()
    {
        $this->assertTrue( is_array(\Resources\Config::myowncfg()) );
    }
}