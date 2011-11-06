<?php
/**
 * Panada Active Record API.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman.
 * @since	Version 0.3
 */
namespace Resources;

class ActiveRecord {
    
    // Define the constants for db relations.
    const BELONGS_TO = 1,
        HAS_ONE = 2,
        HAS_MANY = 3,
        MANY_MANY = 4;
    
    protected $table,
        $connection = 'default',
        $setInstantiateClass = false;
    
    private $db,
        $fields = array(),
        $condition = array(),
        $limit = null,
        $offset = null,
        $select = '*',
        $orderBy = null,
        $order = null,
        $groupBy = array();
    
    public $primaryKey = 'id';
    
    public function __construct(){
        
        $this->cache = new Cache('default', 'default');
        
        // Mendapatkan argument yg diberikan user
        $args = func_get_args();
        
        // Data baru yg akan disave user.
        $newData = array();
        
        // Jika argument pertama diberikan, tipe datanya bisa string ataupun array.
        if( isset($args[0]) && ! empty($args[0]) ){
            
            if( is_array($args[0]) )
                $newData = $args[0];
            else
                $this->connection = $args[0];
            
            // Jika argument ke dua di set, maka itu adalah nama koneksi db-nya.
            if( isset($args[1]) )
                $this->connection = $args[1];
        }
        
        $child_class_name = get_class($this);
        
        // Dapatkan nama tabel dari nama class model
        $this->table    = strtolower( $childClassName );
        
        // Inisialisasi koneksi db
        $this->db       = new Database($this->connection);
        
        // Jika variable $newData tidak kosong, berarti ada data yang akan disave.
        if( ! empty($newData) ){
            
            $this->fields = $newData;
            return $this->save();
        }
        
        if( $relations = $this->relations() )
            foreach( $relations as $relations )
                if( $relations[0] == 1 || $relations[0] == 4 )
                    $this->setInstantiateClass = $childClassName;
    }
    
    /**
     * Return the fields and the value for insert
     * to db
     *
     * @return array
     */
    private function getFields(){
        
        if( empty($this->fields) ){
            
            $this->fields = get_object_vars($this);
            
            unset(
                $this->fields['table'],
                $this->fields['condition'],
                $this->fields['limit'],
                $this->fields['offset'],
                $this->fields['select'],
                $this->fields['orderBy'],
                $this->fields['order'],
                $this->fields['groupBy'],
                $this->fields['classVars'],
                $this->fields['connection'],
                $this->fields['fields'],
                $this->fields['db'],
                $this->fields['primaryKey'],
                $this->fields['setInstantiateClass'],
                $this->fields['cache']
            );
        }
        
        return ! empty($this->fields) ? $this->fields : array();
    }
    
    public function from($tables){
        
        if( is_string($tables) ){
            $this->table = array($this->table, $tables);
            return;
        }
        
        if( is_array($tables) ){
            $tables[] = $this->table;
            $this->table = $tables;
            return;
        }
    }
    
    /**
     * Saving new record to db
     *
     * @return booelan
     */
    public function save(){
        
        $primaryKey = $this->primaryKey;
        
        if( isset($this->$primaryKey) ){
            $return = $this->db->update($this->table, $this->getFields(), array($this->primaryKey => $this->$primaryKey));
            $this->fields = array();
            return $return;
        }
        
        if( $this->db->insert( $this->table, $this->getFields() ) ){
            $insert_id = $this->db->insert_id();
            $this->fields = array();
            return $insert_id;
        }
        
        return false;
    }
    
    /**
     * Get records from db
     *
     * @param array $where
     * @param int $limit
     * @return object if true else false
     */
    public function find(){
        
        $args = func_get_args();
        $total = count($args);
        $cache_key = 'select' . $this->select.$this->table;
        
        $this->db->select( $this->select )->from( $this->table );
        
        if($this->setInstantiateClass){
            $this->db->instantiate_class = $this->setInstantiateClass;
        }
        
        // Kondisi dimana kriteria menggunakan primary key. Hasil yg didapat bisa dipastikan hanya 1 row.
        if( $total == 1 ){
            
            $cache_key .= $this->primaryKey . '=' . $args[0];
            $cache_key = md5($cache_key);
            
            if( $cached = $this->cache->getValue($cache_key) )
                return $cached;
            
            if( ! $return = $this->db->where($this->primaryKey, '=', $args[0])->getOne() )
                return false;
            
            foreach( get_object_vars($return) as $key => $val )
                $this->$key = $val;
            
            $this->cache->setValue($cache_key, $return);
            
            $this->setInstantiateClass = false;
            
            return $return;
        }
        
        // Kondisi dengan jumlah kriteria primary key lebih dari satu. (IN kriteria)
        if( $total > 1 ){
            
            $cache_key .= $this->primaryKey . 'IN' . http_build_query($args);
            $cache_key = md5($cache_key);
            
            if( $cached = $this->cache->getValue($cache_key) )
                return $cached;
            
            $return = $this->db->where($this->primaryKey, 'IN', $args)->getAll();
            
            $this->cache->setValue($cache_key, $return);
            
            $this->setInstantiateClass = false;
            
            return $return;
        }
        
        // Its time for user defined condition implementation.
        if( ! empty($this->condition) ){
            foreach($this->condition as $condition){
                $cache_key .= $condition[0].$condition[1].$condition[2].$condition[3];
                $this->db->where($condition[0], $condition[1], $condition[2], $condition[3]);
            }
            
            unset($this->condition);
        }
        
        if( ! empty($this->groupBy) ){
            $cache_key .= http_build_query($this->groupBy);
            call_user_func_array(array($this->db, 'groupBy'), $this->groupBy);
        }
        
        // Set order if user defined it
        if( ! is_null($this->orderBy) ){
            $cache_key .= $this->orderBy.$this->order;
            $this->db->orderBy($this->orderBy, $this->order);
        }
        
        if( ! is_null($this->limit) ){
            $cache_key .= $this->limit.$this->offset;
            $this->db->limit($this->limit, $this->offset);
        }
        
        $cache_key = md5($cache_key);
        
        if( $cached = $this->cache->getValue( $cache_key ) )
            return $cached;
        
        $return = $this->db->getAll();
        
        $this->cache->setValue($cache_key, $return);
        
        $this->setInstantiateClass = false;
        
        return $return;
    }
    
    /**
     * Delete record base on $args or $this->condition var
     * Criteria
     *
     * @param mix $args
     * @return boolean
     */
    public function delete( $args = null ){
        
        if( ! empty($this->condition) ){
            
            foreach($this->condition as $condition)
                $this->db->where($condition[0], $condition[1], $condition[2], $condition[3]);
            
            unset($this->condition);
            $condition = null;
        }
        
        else if( is_array($args) )
            $condition = $args;
        
        else if( ! is_null($args) )
            $condition = array( $this->primaryKey => $args );
        
        return $this->db->delete($this->table, $condition); 
    }
    
    /**
     * Update recored without assigning the values
     * into class properties.
     *
     * @param mix $args
     * @return boolean
     */
    public function update( $args = null ){
        
        if( ! empty($this->condition) ){
            
            foreach($this->condition as $condition)
                $this->db->where($condition[0], $condition[1], $condition[2], $condition[3]);
            
            unset($this->condition);
            $condition = null;
        }
        
        else if( is_array($args) )
            $condition = $args;
        
        else if( ! is_null($args) )
            $condition = array( $this->primaryKey => $args );
        
        return $this->db->update(
                            $this->table,
                            $this->getFields(),
                            $condition
                        );
    }
    
    /**
     * Set condition.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param mix $separator
     * @return object
     */
    public function condition( $column, $operator, $value, $separator = false ){
        
        $args = array($column, $operator, $value, $separator);
        $this->condition[] = $args;
        return $this;
    }
    
    /**
     * Short the results.
     *
     * @param string $column
     * @param string $order ASC | DESC
     * @return object
     */
    public function order($column, $order = null){
	
	$this->orderBy = $column;
	$this->order = $order;
        return $this;
    }
    
    /**
     * Select certain column
     *
     * @param string | array $select
     * @return object
     */
    public function select(){
        
        $select = func_get_args();
        
        if( empty($select) )
            $select = '*';
            
        $this->select = $select;
        return $this;
    }
    
    /**
     * Limit the results
     *
     * @param int $limit
     * @param int $offset
     * @return object
     */
    public function limit($limit, $offset = null){
        
        $this->limit = $limit;
	$this->offset = $offset;
        return $this;
    }
    
    /**
     * Group the results
     * 
     * @param string $column1, $column2 etc ...
     * @return object
     */
    public function group(){
        
        $this->groupBy = func_get_args();
        return $this;
    }
    
    /**
     * Dynamic finder method hendler
     *
     * @param string $name Method name
     * @param array $arguments Method arguments
     */
    public function __call( $name, $arguments = array() ){
        
        $cache_key = 'select' . $this->select.$this->table;
        $this->db->select( $this->select )->from($this->table);
        
        if($name == 'first'){
            $cache_key .= $this->primaryKey.'ASC';
            return $this->db->orderBy($this->primaryKey, 'ASC')->limit(1)->getOne();
        }
        
        if($name == 'last'){
            $cache_key .= $this->primaryKey.'DESC';
            return $this->db->orderBy($this->primaryKey, 'DESC')->limit(1)->getOne();
        }
        
        $split_name = explode('find_by_', strtolower($name) );
        
        if( count($split_name) > 1 ){
            
            if( empty($arguments) )
                trigger_error("find_by_<b>column_name</b>() in Active Record method expects 1 parameter and you dont given anything yet.", E_USER_ERROR);
            
            $cache_key .= $split_name[1] . '=' . $arguments[0];
            $this->db->where($split_name[1], '=', $arguments[0]);
            
            if( ! is_null($this->limit) ){
                $cache_key .= $this->limit.$this->offset;
                $this->db->limit($this->limit, $this->offset);
            }
            
            if($this->setInstantiateClass)
                $this->db->instantiate_class = $this->setInstantiateClass;
            
            $cache_key = md5($cache_key);
            
            if( $cached = $this->cache->getValue( $cache_key ) )
                return $cached;
        
            $results = $this->db->getAll();
            $this->setInstantiateClass = false;
            
            if( count($results) == 1 ){
                
                $pk = $this->primaryKey;
                $this->$pk = $results[0]->$pk;
                
                $this->cache->setValue($cache_key, $results[0]);
                
                return $results[0];
            }
            
            $this->cache->setValue($cache_key, $results);
            return $results;
            
        }
    }
    
    /**
     * overrided method for relations scheme
     */
    public function relations(){
        
        return false;
    }
    
    /**
     * Magic method for lazy call relations.
     *
     * @param string $name Property name
     * @return mix
     */
    public function __get( $name = false ){
        
        if( ! $name )
            return false;
        
        if( ! $relations = $this->relations() )
            return false;
        
        foreach($relations as $key => $relations){
            if( $name == $key ){
                
                $class_name = 'Model_'.$relations[1];
                
                $name = new $class_name;
                $find_by = 'find_by_'.$name->primaryKey;
                
                if( $relations[0] == 1 ){
                    
                    $name->limit(1);
                    return $name->$find_by( $this->$relations[2] );
                }
                elseif( $relations[0] == 2 ){
                    
                    $find_by = 'find_by_'.$relations[2];
                    $pk = $this->primaryKey;
                    
                    $name->limit(1);
                    return $name->$find_by( $this->$pk );
                }
                elseif( $relations[0] == 3 ){
                    
                    $pk = $this->primaryKey;
                    $name->condition($relations[2], '=', $this->$pk, 'AND');
                    return $name;
                }
                elseif( $relations[0] == 4 ){
                    
                    $pk = $this->primaryKey;
                    
                    $name->select($relations[1].'.*');
                    $name->from( $relations[2][0] );
                    $name->condition($relations[2][0].'.'.$relations[2][2], '=', $this->$pk, 'AND');
                    $name->condition($relations[2][0].'.'.$relations[2][1], '=', $relations[1].'.'.$name->primaryKey, 'AND');
                    
                    return $name;
                }
            }
        }
    }
    
}