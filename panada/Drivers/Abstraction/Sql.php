<?php

/**
 * Panada SQL Database Abstraction.
 *
 * @author	Iskandar Soesman.
 *
 * @since	Version 1.1.0
 */
namespace Drivers\Abstraction;

abstract class Sql
{
    protected $column = '*';
    protected $distinct = false;
    protected $tables = array();
    protected $joins = null;
    protected $joinsType = null;
    protected $joinsOn = array();
    protected $criteria = array();
    protected $groupBy = null;
    protected $isHaving = array();
    protected $limit = null;
    protected $offset = null;
    protected $orderBy = null;
    protected $order = null;
    protected $isQuotes = true;
    protected $link;
    protected $connection;
    protected $config;
    protected $lastQuery;
    protected $lastError;
    protected $throwError = false;
    protected $returnType = 'object'; // return data type option: object, array and iterator (pointer)
    public $insertId;
    public $clientFlags = 0;
    public $newLink = true;
    public $persistentConnection = false;
    public $instantiateClass = 'stdClass';

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
     * API for "SELECT ... " statement.
     *
     * @param string $column1, $column2 etc ...
     *
     * @return object
     */
    public function select()
    {
        $column = func_get_args();

        if (!empty($column)) {
            $this->column = $column;

            if (is_array($column[0])) {
                $this->column = $column[0];
            }
        }

        return $this;
    }

    /**
     * API for "... DISTINCT " statement.
     *
     * @return object
     */
    public function distinct()
    {
        $this->distinct = true;

        return $this;
    }

    /**
     * API for "...FROM ... " statement.
     *
     * @param string $table1, $table2 etc ...
     *
     * @return object
     */
    public function from()
    {
        $tables = func_get_args();

        if (is_array($tables[0])) {
            $tables = $tables[0];
        }

        $this->tables = $tables;

        return $this;
    }

    /**
     * API for "... JOIN ..." statement.
     *
     * @param string $table Table to join
     * @param string $type  Type of join: LEFT, RIGHT, INNER
     */
    public function join($table, $type = null)
    {
        $this->joins = $table;
        $this->joinsType = $type;

        return $this;
    }

    /**
     * Create criteria condition. It use in on, where and having method.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param mix    $separator
     */
    protected function createCriteria($column, $operator, $value, $separator)
    {
        if (is_string($value) && $this->isQuotes) {
            $value = $this->escape($value);
            $value = " '$value'";
        }

        $operator = strtoupper($operator);

        if ($operator == 'IN') {
            if (is_array($value)) {
                $value = "('".implode("', '", $value)."')";
            }
        }

        if ($operator == 'BETWEEN') {
            $value = $value[0].' AND '.$value[1];
        }

        $return = $column.' '.$operator.' '.$value;

        if ($separator) {
            $return .= ' '.strtoupper($separator);
        }

        return $return;
    }

    /**
     * API for "... JOIN ON..." statement.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param mix    $separator
     */
    public function on($column, $operator, $value, $separator = false)
    {
        $this->isQuotes = false;
        $this->joinsOn[] = $this->createCriteria($column, $operator, $value, $separator);
        $this->isQuotes = true;

        return $this;
    }

    /**
     * API for "... WHERE ... " statement.
     *
     * @param string $column    Column name
     * @param string $operator  SQL operator string: =,<,>,<= dll
     * @param string $value     Where value
     * @param string $separator Such as: AND, OR
     *
     * @return object
     */
    public function where($column, $operator, $value, $separator = false)
    {
        if (is_string($value)) {
            $value_arr = explode('.', $value);
            if (count($value_arr) > 1) {
                if (array_search($value_arr[0], $this->tables) !== false) {
                    $this->isQuotes = false;
                }
            }
        }

        $this->criteria[] = $this->createCriteria($column, $operator, $value, $separator);
        $this->isQuotes = true;

        return $this;
    }

    /**
     * API for "... GROUP BY ... " statement.
     *
     * @param string $column1, $column2 etc ...
     *
     * @return object
     */
    public function groupBy()
    {
        $this->groupBy = implode(', ', func_get_args());

        return $this;
    }

    /**
     * API for "... HAVING..." statement.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param mix    $separator
     */
    public function having($column, $operator, $value, $separator = false)
    {
        $this->isHaving[] = $this->createCriteria($column, $operator, $value, $separator);

        return $this;
    }

    /**
     * API for "... ORDER BY..." statement.
     *
     * @param string $column1, $column2 etc ...
     *
     * @return object
     */
    public function orderBy($column, $order = null)
    {
        $this->orderBy = $column;
        $this->order = $order;

        return $this;
    }

    /**
     * API for "... LIMIT ..." statement.
     *
     * @param int
     * @param int Optional offset value
     *
     * @return object
     */
    public function limit($limit, $offset = null)
    {
        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    /**
     * Build the SQL statement.
     *
     * @return string The complited SQL statement
     */
    public function command()
    {
        $query = 'SELECT ';

        if ($this->distinct) {
            $query .= 'DISTINCT ';
            $this->distinct = false;
        }

        $column = '*';

        if (is_array($this->column)) {
            $column = implode(', ', $this->column);
            $this->column = '*';
        }

        $query .= $column;

        if (!empty($this->tables)) {
            $query .= ' FROM '.implode(', ', $this->tables);
            $this->tables = array();
        }

        if (!is_null($this->joins)) {
            if (!is_null($this->joinsType)) {
                $query .= ' '.strtoupper($this->joinsType);
                $this->joinsType = null;
            }

            $query .= ' JOIN '.$this->joins;

            if (!empty($this->joinsOn)) {
                $query .= ' ON ('.implode(' ', $this->joinsOn).')';
                $this->joinsOn = array();
            }

            $this->joins = null;
        }

        if (!empty($this->criteria)) {
            $cr = implode(' ', $this->criteria);
            $query .= ' WHERE '.rtrim(rtrim($cr, 'AND'), 'OR');
            $this->criteria = array();
        }

        if (!is_null($this->groupBy)) {
            $query .= ' GROUP BY '.$this->groupBy;
            $this->groupBy = null;
        }

        if (!empty($this->isHaving)) {
            $query .= ' HAVING '.implode(' ', $this->isHaving);
            $this->isHaving = array();
        }

        if (!is_null($this->orderBy)) {
            $query .= ' ORDER BY '.$this->orderBy.' '.strtoupper($this->order);
            $this->orderBy = null;
        }

        if (!is_null($this->limit)) {
            $query .= ' LIMIT '.$this->limit;

            if (!is_null($this->offset)) {
                $query .= ' OFFSET '.$this->offset;
                $this->offset = null;
            }

            $this->limit = null;
        }

	$this->returnType = 'object';

        return $query;
    }

    /**
     * Flag to get return type. The options is: object, array and iterator.
     *
     * @param string $returnType
     *
     * @return object
     */
    public function fetchAs($returnType = 'object')
    {
        $this->returnType = $returnType;

        return $this;
    }

    /**
     * Previously called get_results.
     *
     * @since Version 0.3.1
     *
     * @param mix
     * @param array
     * @param array
     *
     * @return object
     */
    public function getAll($table = false, $where = array(), $fields = array(), $returnType = false)
    {
        if (!$table) {
	    $result = $this->results($this->command());
            $this->returnType = 'object';
            
            return $result;
        }

        if ($returnType) {
            $this->returnType = $returnType;
        }

        $column = '*';

        if (!empty($fields)) {
            $column = $fields;
        }

        $this->select($column)->from($table);

        if (!empty($where)) {
            foreach ($where as $key => $val) {
                $this->where($key, '=', $val, 'AND');
            }
        }

        return $this->getAll();
    }

    /**
     * Previously called get_row.
     *
     * @since Version 0.3.1
     *
     * @param mix
     * @param array
     * @param array
     *
     * @return object
     */
    public function getOne($table = false, $where = array(), $fields = array(), $returnType = false)
    {
        if (!$table) {
	    $row = $this->row($this->command());
            $this->returnType = 'object';

            return $row;
        }

        if ($returnType) {
            $this->returnType = $returnType;
        }

        $column = '*';

        if (!empty($fields)) {
            $column = $fields;
        }

        $this->select($column)->from($table);

        if (!empty($where)) {
            foreach ($where as $key => $val) {
                $this->where($key, '=', $val, 'AND');
            }
        }

        return $this->getOne();
    }

    /**
     * Get value directly from single field. Previusly called get_var().
     *
     * @since Version 0.3.1
     *
     * @param string @query
     *
     * @return string|int Depen on it record value.
     */
    public function getVar($query = null)
    {
        if (is_null($query)) {
            $query = $this->command();
        }

        $result = $this->row($query);
        $key = array_keys(get_object_vars($result));

        return $result->$key[0];
    }

    /**
     * Abstraction for insert.
     *
     * @param string $table
     * @param array  $data
     *
     * @return bool
     */
    public function insert($table, $data = array())
    {
        $fields = array_keys($data);

        foreach ($data as $key => $val) {
            $escaped_date[$key] = $this->escape($val);
        }

        return $this->query("INSERT INTO $table (".implode(',', $fields).") VALUES ('".implode("','", $escaped_date)."')");
    }

    /**
     * Abstraction for update.
     *
     * @param string $table
     * @param array  $dat
     * @param array  $where
     *
     * @return bool
     */
    public function update($table, $dat, $where = null)
    {
        foreach ($dat as $key => $val) {
            $data[$key] = $this->escape($val);
        }

        $bits = $wheres = array();
        foreach ((array) array_keys($data) as $k) {
            $bits[] = "$k = '$data[$k]'";
        }

        if (!empty($this->criteria)) {
            $criteria = implode(' ', $this->criteria);
            unset($this->criteria);
        } elseif (is_array($where)) {
            foreach ($where as $c => $v) {
                $wheres[] = "$c = '".$this->escape($v)."'";
            }

            $criteria = implode(' AND ', $wheres);
        } else {
            return false;
        }

        return $this->query("UPDATE $table SET ".implode(', ', $bits).' WHERE '.$criteria);
    }

    /**
     * Abstraction for delete.
     *
     * @param string
     * @param array
     *
     * @return bool
     */
    public function delete($table, $where = null)
    {
        if (!empty($this->criteria)) {
            $criteria = implode(' ', $this->criteria);
            unset($this->criteria);
        } elseif (is_array($where)) {
            foreach ($where as $c => $v) {
                $wheres[] = "$c = '".$this->escape($v)."'";
            }

            $criteria = implode(' AND ', $wheres);
        } else {
            return false;
        }

        return $this->query("DELETE FROM $table WHERE ".$criteria);
    }

    /**
     * Print the error.
     *
     * @return string
     */
    protected function printError()
    {
        if ($caller = \Resources\RunException::getErrorCaller(5)) {
            $error_str = sprintf('Database error %1$s for query %2$s made by %3$s', $this->lastError, $this->lastQuery, $caller);
        } else {
            $error_str = sprintf('Database error %1$s for query %2$s', $this->lastError, $this->lastQuery);
        }

        \Resources\RunException::outputError($error_str);
    }

    /**
     * Get last query.
     *
     * @return string
     */
    public function getLastQuery()
    {
        return $this->lastQuery;
    }
}
