<?php
namespace Tests\Database;

class Connection extends \PHPUnit_Framework_TestCase
{
    public $connection = 'default';

    public function setUp()
    {
        new \Test\Bootstrap;

        $this->db = new \Resources\Database($this->connection);
    }

    public function testConnection()
    {
        $data = $this->db->version();

        $this->assertTrue( (is_string($data) && ! empty($data)) );

        return $data;
    }
}
