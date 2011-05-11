<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada Active Record API.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman.
 * @since	Version 0.3
 */

class Library_active_record {
    
    // Define the constants for db relations.
    const BELONGS_TO = 1;
    const HAS_ONE = 2;
    const HAS_MANY = 3;
    const MANY_MANY = 4;
    
    protected $table;
    protected $connection = 'default';
    
    private $db;
    private $fields = array();
    private $condition = array();
    private $limit = null;
    private $offset = null;
    private $select = '*';
    private $order_by = null;
    private $order = null;
    private $group_by = array();
    
    public $primary_key = 'id';
    
    public function __construct(){
        
        // Mendapatkan argument yg diberikan user
        $args = func_get_args();
        
        // Data baru yg akan disave user.
        $new_data = array();
        
        // Jika argument pertama diberikan, tipe datanya bisa string ataupun array.
        if( isset($args[0]) && ! empty($args[0]) ){
            
            if( is_array($args[0]) )
                $new_data = $args[0];
            else
                $this->connection = $args[0];
            
            // Jika argument ke dua di set, maka itu adalah nama koneksi db-nya.
            if( isset($args[1]) )
                $this->connection = $args[1];
        }
        
        // Dapatkan nama tabel dari nama class model
        $this->table    = str_ireplace( 'Model_', '', get_class($this) );
        
        // Inisialisasi koneksi db
        $this->db       = new Library_db($this->connection);
        
        // Jika variable $new_data tidak kosong, berarti ada data yang akan disave.
        if( ! empty($new_data) ){
            
            $this->fields = $new_data;
            return $this->save();
        }
    }
    
    /**
     * Return the fields and the value for insert
     * to db
     *
     * @return array
     */
    private function get_fields(){
        
        if( empty($this->fields) ){
            
            $this->fields = get_object_vars($this);
            
            unset(
                $this->fields['table'],
                $this->fields['condition'],
                $this->fields['limit'],
                $this->fields['offset'],
                $this->fields['select'],
                $this->fields['order_by'],
                $this->fields['order'],
                $this->fields['group_by'],
                $this->fields['class_vars'],
                $this->fields['connection'],
                $this->fields['fields'],
                $this->fields['db'],
                $this->fields['primary_key']
            );
        }
        
        return ! empty($this->fields) ? $this->fields : array();
    }
    
    /**
     * Saving new record to db
     *
     * @return booelan
     */
    public function save(){
        
        $primary_key = $this->primary_key;
        
        if( isset($this->$primary_key) )
            return $this->db->update($this->table, $this->get_fields(), array($this->primary_key => $this->$primary_key)); 
        
        return $this->db->insert( $this->table, $this->get_fields() );
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
        
        $this->db->select( $this->select )->from( $this->table );
        
        if( $total == 1 ){
            
            if( ! $return = $this->db->where($this->primary_key, '=', $args[0])->row() )
                return false;
            
            foreach( get_object_vars($return) as $key => $val )
                $this->$key = $val;
            
            return $return;
        }
        
        if( $total > 1 )
            return $this->db->where($this->primary_key, 'IN', $args)->results();
        
        // Its time for user defined condition implementation.
        if( ! empty($this->condition) ){
            foreach($this->condition as $condition)
                $this->db->where($condition[0], $condition[1], $condition[2], $condition[3]);
            
            unset($this->condition);
        }
        
        if( ! empty($this->group_by) )
            call_user_func_array(array($this->db, 'group_by'), $this->group_by);
        
        // Set order if user defined it
        if( ! is_null($this->order_by) )
            $this->db->order_by($this->order_by, $this->order);
        
        if( ! is_null($this->limit) )
            $this->db->limit($this->limit, $this->offset);
        
        return $this->db->results();
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
            $condition = array( $this->primary_key => $args );
        
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
            $condition = array( $this->primary_key => $args );
        
        return $this->db->update(
                            $this->table,
                            $this->get_fields(),
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
	
	$this->order_by = $column;
	$this->order = $order;
        return $this;
    }
    
    /**
     * Select certain column
     *
     * @param string | array $select
     * @return object
     */
    public function select($select = '*'){
        
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
        
        $this->group_by = func_get_args();
        return $this;
    }
    
    /**
     * Dynamic finder method hendler
     *
     * @param string $name Method name
     * @param array $arguments Method arguments
     */
    public function __call( $name, $arguments = array() ){
        
        $this->db->select()->from($this->table);
        
        if($name == 'first')
            return $this->db->order_by($this->primary_key, 'ASC')->limit(1)->row();
        
        if($name == 'last')
            return $this->db->order_by($this->primary_key, 'DESC')->limit(1)->row();
        
        $split_name = explode('find_by_', strtolower($name) );
        
        if( count($split_name) > 1 ){
            
            if( empty($arguments) )
                trigger_error("find_by_<b>column_name</b>() in Active Record method expects 1 parameter and you dont given anything yet.", E_USER_ERROR);
            
            $this->db->where($split_name[1], '=', $arguments[0]);
            
            if( ! is_null($this->limit) )
                $this->db->limit($this->limit, $this->offset);
            
            $results = $this->db->results();
            
            if( count($results) == 1 )
                return $results[0];
            
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
        
        foreach($relations as $key => $relations)
            if( $name == $key ){
                
                $class_name = 'Model_'.$relations[1];
                $name = new $class_name;
                $find_by = 'find_by_'.$name->primary_key;
                
                if( $relations[0] == 1 ){
                    $name->limit(1);
                    return $name->$find_by( $this->$relations[2] );
                }
                elseif( $relations[0] == 2 ){
                    $name->limit(1);
                    return $name->$find_by( $this->$relations[2] );
                }
                elseif( $relations[0] == 3 ){
                    
                    $name->condition($name->primary_key, '=', 1);
                    return $name;
                }
                elseif( $relations[0] == 4 ){
                   
                    return $name->$find_by( $this->$relations[2] );
                }
            }
    }
}