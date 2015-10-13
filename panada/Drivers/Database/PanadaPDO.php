<?php

/**
 * Panada PDO Database Driver.
 *
 * @author	Azhari Harahap <azhari@harahap.us>
 *
 * @since	Version 1.0
 */
namespace Drivers\Database;

use Resources\RunException as RunException;
use PDO;
use PDOException;

class PanadaPDO extends \Drivers\Abstraction\Sql implements \Resources\Interfaces\Database
{
    protected $port = null;
    private $dsn;

    /**
     * Check if PDO enabled
     * Define all properties needed.
     */
    public function __construct($config, $connectionName)
    {
        // Check for PDO
    if (!extension_loaded('PDO')) {
        throw new RunException('PDO extension not installed.');
    }

        $this->config = $config;
        $this->connection = $connectionName;
    }

    /**
     * Establish a new connection.
     *
     * @return string | boolean
     */
    private function establishConnection()
    {
        // Persistent connection?
    $options[PDO::ATTR_PERSISTENT] = $this->config['persistent'];

    // Build DSN
    $this->dsn = $this->config['driver'].':host='.$this->config['host'].
        ';port='.$this->config['port'].';dbname='.$this->config['database'];

        try {
            return new PDO($this->dsn, $this->config['user'], $this->config['password'], $options);
        } catch (PDOException $e) {
            throw new RunException($e->getMessage());
        }
    }

    /**
     * Initial for all process.
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
        $this->link->beginTransaction();
    }

    /**
     * Commit transaction.
     */
    public function commit()
    {
        $this->link->commit();
    }

    public function rollback()
    {
        $this->link->rollBack();
    }

    /**
     * Escape all unescaped string.
     *
     * @param string $string
     */
    public function escape($string)
    {
        return $string;
    }

    public function query($sql)
    {
        if (is_null($this->link)) {
            $this->init();
        }

        $query = $this->link->query($sql);
        $this->lastQuery = $sql;

        if ($this->link->errorCode() != 00000) {
            $this->lastError = implode(' ', $this->link->errorInfo());

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

        if ($this->returnType == 'object' || $this->returnType == 'array') {
            $fetch = $this->returnType == 'object' ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC;

            while ($row = $result->fetch($fetch)) {
                $return[] = $row;
            }

            return $return;
        }

        if ($this->returnType == 'iterator') {
            return $result;
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

        return $result->fetch($this->returnType == 'object' ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC);
    }

    /**
     * Get the id form last insert.
     *
     * @return int
     */
    public function insertId()
    {
        return $this->link->lastInsertId();
    }

    /**
     * Get this db version.
     */
    public function version()
    {
        return $this->link->getAttribute(constant('PDO::ATTR_SERVER_VERSION'));
    }

    /**
     * Close db connection.
     */
    public function close()
    {
        $this->link = null;
    }

    /**
     * Prepares a statement for execution and returns a statement object.
     *
     * @return PDOStatement
     */
    public function prepare($query)
    {
        if (is_null($this->link)) {
            $this->init();
        }

        return $this->link->prepare($query);
    }
}
