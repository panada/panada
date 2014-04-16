<?php
namespace Tests\Database;

class MysqlTest extends Write
{
    public $connection = 'default';
    
    public function __construct()
    {
        new \Tests\Bootstrap;
        
        $this->db = new \Resources\Database();
        
        $this->db->query('DROP TABLE users'); 
        
        $this->db->query('CREATE TABLE users (
            id INTEGER NOT NULL AUTO_INCREMENT,
            name varchar(50) NOT NULL,
            email varchar(50) NOT NULL,
            password varchar(32) NOT NULL,
            PRIMARY KEY (id)
        )');
    }
}
