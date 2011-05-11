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
    
    protected $table;
    protected $condition = array();
    protected $limit = null;
    protected $offset = null;
    protected $select = '*';
    protected $order_by = null;
    protected $order = null;
    protected $group_by = array();
    
    private $class_vars;
    private $connection;
    private $fields = array();
    private $db;
    public $primary_key = 'id';
    
    public function __construct( $instance = false, $connection = 'default', $data = array() ){
        
        if( ! $instance )
            return false;
        
        $this->connection   = $connection;
        $this->class_vars   = $instance;
        $this->table        = str_replace('AR_', '', get_class($this));
        $this->db           = new Library_db($connection);
        
        if( ! empty($data) ){
            $this->fields = $data;
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
            
            $this->fields = get_object_vars($this->class_vars);
            
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
        
        if( $this->$primary_key )
            return $this->db->update($this->table, $this->get_fields(), array($this->primary_key => $this->$primary_key)); 
        
        return $this->db->insert( $this->table, $this->get_fields() );
    }
    
    protected function db_fields_to_properties( $db_objcet ){
        
        if( is_object($db_objcet) ){
            foreach( get_object_vars($db_objcet) as $key => $val )
                $this->$key = $val;
            
            return;
        }
        
        if( is_array($db_objcet) )
            foreach($db_objcet as $db_objcet)
                $this->db_fields_to_properties($db_object);
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
     * Update recored tanpa perlu meng-assign
     * datanya ke dalam class terlebih dahulu.
     *
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
    
    public function find_by_sql(){
        
    }
    
    // Condition = where
    public function condition( $column, $operator, $value, $separator = false ){
        
        $args = array($column, $operator, $value, $separator);
        $this->condition[] = $args;
        return $this;
    }
    
    public function order($column, $order = null){
	
	$this->order_by = $column;
	$this->order = $order;
        return $this;
    }
    
    public function select($select = '*'){
        
        $this->select = $select;
        return $this;
    }
    
    public function limit($limit, $offset = null){
        
        $this->limit = $limit;
	$this->offset = $offset;
        return $this;
    }
    
    public function group(){
        
        $this->group_by = func_get_args();
        return $this;
    }
    
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
            
            $results = $this->db->where($split_name[1], '=', $arguments[0])->results();
            
            if( count($results) == 1 )
                return $results[0];
            
            return $results;
            
        }
    }
}