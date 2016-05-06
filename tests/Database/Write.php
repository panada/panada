<?php
namespace Tests\Database;

class Write extends \PHPUnit_Framework_TestCase
{
    public function data()
    {
        return array(
            array('name' => 'jhon', 'email' => 'jhon@doe.com', 'password' => '123'),
            array('name' => 'smith', 'email' => 'smith@jhon.com', 'password' => '456'),
            array('name' => 'foo', 'email' => 'foo@bar.com', 'password' => '789'),
            array('name' => 'bar', 'email' => 'bar@foo.com', 'password' => '101'),
        );
    }
    
    public function testQBInsertOneRow()
    {
        $newData = $this->data();
        $newData = $newData[0];
        
        $this->db->insert('users', $newData);
        
        $insertId = $this->db->insertId();
        
        $data = $this->db->getOne( 'users', array('id' => $insertId), array('name', 'email', 'password'), 'array' );
        
        $this->assertTrue($data == $newData);
    }
    
    public function testQBInsertAll()
    {
        foreach( $this->data() as $data) {
            $this->db->insert('users', $data);
        }
        
        $data = $this->db->getAll('users');
        
        $this->assertGreaterThanOrEqual(4, count($data));
    }
    
    public function testQBUpdate()
    {
        $criteria = array('id' => 1);
        $newData = array('name' => 'jhon gmail', 'email' => 'jhon@gmail.com');
        
        $this->db->update(
            'users',
            $newData,
            $criteria
        );
        
        $data = $this->db->getOne( 'users', $criteria, array('name', 'email'), 'array' );
        
        $this->assertTrue($data == $newData);
    }
}
