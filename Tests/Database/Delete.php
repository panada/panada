<?php
namespace Tests\Database;

class Delete extends Read
{
    public function testQBDeleteOne()
    {
        $data = $this->db->select()->from('users')->orderBy('id', 'DESC')->limit(1)->getOne();
        
        $criteria = array('id' => $data->id);
        
        $this->db->delete('users', $criteria);
        
        $data = $this->db->getOne('users', $criteria);
        
        $this->assertNull($data);
    }
}
