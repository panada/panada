<?php
namespace Tests\Database;

class Read extends Write
{
    public function testQBSelectAll()
    {
        $data = $this->db->select()->from('users')->getAll();
        
        $this->assertArrayHasKey(0, $data);
    }
    
    public function testQBSelectByField()
    {
        $data = $this->db->select('id', 'name')->from('users')->getAll();
        
        $this->assertEquals( array('id', 'name'), array_keys(get_object_vars($data[0])) );
    }
    
    public function testQBSelectByFieldArray()
    {
        $data = $this->db->select('id', 'name')->from('users')->getAll();
        
        $this->assertEquals( array('id', 'name'), array_keys(get_object_vars($data[0])) );
    }
    
    public function testQBSelectWithSQL()
    {
        $data = $this->db->select('COUNT(*)')->from('users')->getVar();
        
        $this->assertNotEmpty($data);
    }
    
    public function testQBWhereGetOne()
    {
        $data = $this->db->select()->from('users')->where('id', '=', 1)->getOne();
        $this->assertTrue( is_object($data) );
    }
    
    public function testQBOrderBy()
    {
        $data = $this->db->select()->from('users')->orderBy('id')->getAll();
        
        $this->assertArrayHasKey(0, $data);
    }
    
    public function testQBLimit()
    {
        $data = $this->db->select()->from('users')->where('id', '>', 1)->orderBy('id', 'DESC')->limit(3)->getAll();
        
        $this->assertArrayHasKey(0, $data);
    }
    
    public function testQBPassTableNameToGetALL()
    {
        $args = array(
            'limit' => 10,
            'page' => 1,
            'orderBy' => 'id',
            'sort' => 'ASC',
            'criteria' => array(),
            'fields' => array()
        );
       
        //$args = array_merge($default, $args);
       
        $offset = ($args['limit'] * $args['page']) - $args['limit'];      
     
        $data = $this->db
                ->orderBy($args['orderBy'], $args['sort'])
                ->limit($args['limit'], $offset)
                ->getAll('users', $args['criteria'], $args['fields']);
        
        $this->assertArrayHasKey(0, $data);
    }
    
    public function testNatGetSingleDataWithRow()
    {
        $data = $this->db->row("SELECT * FROM users WHERE id = '1'");
        
        $this->assertTrue( is_object($data) );
    }
    
    public function testNatGetSingleDataWithRowAsArray()
    {
        $data = $this->db->row("SELECT * FROM users WHERE id = '1'", 'array');
        
        $this->assertTrue( is_array($data) );
    }
    
    public function testNatGetMultipleData()
    {
        $data = $this->db->results("SELECT * FROM users");
        
        $this->assertTrue( is_array($data) );
    }
}
