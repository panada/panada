<?php

/**
 * Panada PostgreSQL Database Driver.
 *
 * @author	Iskandar Soesman.
 *
 * @since	Version 0.3
 */
namespace Drivers\Database;

use Resources\RunException as RunException;

class Pgsql extends \Drivers\Abstraction\Sql implements \Resources\Interfaces\Database
{
    protected $port = 5432;

    /**
     * Define all properties needed.
     */
    public function __construct($config, $connectionName)
    {
        $this->config = $config;
        $this->connection = $connectionName;
    }

    /**
     * Throw the error instead handle it automaticly.
     * User should catch this error for there own purpose.
     *
     * @param bool $set
     */
    public function setThrowError($set = false)
    {
        $this->throwError = $set;
    }

    /**
     * Establish a new connection to postgreSQL server.
     *
     * @return string | boolean postgreSQL persistent link identifier on success, or FALSE on failure.
     */
    private function establishConnection()
    {
        $function = ($this->config['persistent']) ? 'pg_pconnect' : 'pg_connect';

        $connection = $function(
            'host='.$this->config['host'].
            ' port='.$this->config['port'].
            ' dbname='.$this->config['database'].
            ' user='.$this->config['user'].
            ' password='.$this->config['password'],
            false);

        pg_set_client_encoding($connection, $this->config['charset']);

        return $connection;
    }

    /**
     * Inital for all process.
     */
    private function init()
    {
        if (is_null($this->link)) {
            $this->link = $this->establishConnection();
        }

        try {
            if (!$this->link) {
                throw new RunException('Unable connect to database in <strong>'.$this->connection.'</strong> connection.');
            }
        } catch (RunException $e) {
            RunException::outputError($e->getMessage());
        }
    }

    /**
     * Start transaction.
     */
    public function begin()
    {
        pg_query($this->link, 'begin');
    }

    /**
     * Commit transaction.
     */
    public function commit()
    {
        pg_query($this->link, 'commit');
    }

    /**
     * Rollback transaction.
     */
    public function rollback()
    {
        pg_query($this->link, 'rollback');
    }

    /**
     * Escape all unescaped string.
     *
     * @param string $string
     */
    public function escape($string)
    {
        if (is_null($this->link)) {
            $this->init();
        }

        return pg_escape_string($this->link, $string);
    }

    /**
     * Main function for querying to database.
     *
     * @param $query The SQL querey statement
     *
     * @return string|objet Return the resource id of query
     */
    public function query($sql)
    {
        if (is_null($this->link)) {
            $this->init();
        }

        $query = pg_query($this->link, $sql);
        $this->lastQuery = $sql;

        if ($this->lastError = pg_last_error($this->link)) {
            if ($this->throwError) {
                throw new \Exception($this->lastError);
            } else {
                $this->printError();

                return false;
            }
        }

        return $query;
    }

    /**
     * Get multiple records.
     *
     * @param string $query The sql query
     * @param string $type  return data type option. the default is "object"
     */
    public function results($query, $returnType = false)
    {
        $return = false;

        if ($returnType) {
            $this->returnType = $returnType;
        }

        if (is_null($query)) {
            $query = $this->command();
        }

        $result = $this->query($query);

        if ($this->returnType == 'object') {
            while ($row = pg_fetch_object($result, null, $this->instantiateClass)) {
                $return[] = $row;
            }

            pg_free_result($result);

            return $return;
        }

        if ($this->returnType == 'iterator') {
            return $result;
        }

        if ($this->returnType == 'array') {
            while ($row = pg_fetch_assoc($result)) {
                $return[] = $row;
            }

            pg_free_result($result);

            return $return;
        }

        return $return;
    }

    /**
     * Get single record.
     *
     * @param string $query The sql query
     * @param string $type  return data type option. the default is "object"
     */
    public function row($query, $returnType = false)
    {
        if ($returnType) {
            $this->returnType = $returnType;
        }

        if (is_null($query)) {
            $query = $this->command();
        }

        if (is_null($this->link)) {
            $this->init();
        }

        $result = $this->query($query);
        $return = pg_fetch_object($result, null, $this->instantiateClass);

        if ($this->returnType == 'object') {
            return $return;
        }

        return (array) $return;
    }

    /**
     * Get the id form last insert.
     *
     * @return int
     */
    public function insertId()
    {
        return $this->getVar('SELECT LASTVAL() as ins_id');
    }

    /**
     * Get this db version.
     */
    public function version()
    {
        return $this->getVar('SELECT version() AS version');
    }

    /**
     * Close db connection.
     */
    public function close()
    {
        pg_close($this->link);
    }
}
