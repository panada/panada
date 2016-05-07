<?php
namespace Tests\Database;

class PgsqlTest extends Write
{
    public $connection = 'pqsql';

    public function __construct()
    {
        new \Tests\Bootstrap;

        $this->db = new \Resources\Database($this->connection);

        $this->db->query('DROP TABLE IF EXISTS users');

        $this->db->query('CREATE TABLE users (
            id SERIAL NOT NULL,
            name varchar(50) NOT NULL,
            email varchar(50) NOT NULL,
            password varchar(32) NOT NULL,
            PRIMARY KEY (id)
        )');
    }
}
