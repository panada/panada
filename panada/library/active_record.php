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
    public $primary_key = 'id';
    private $class_vars;
    private $connection;
    private $fields = array();
    private $db;
    
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
        
        $this->db->select()->from($this->table);
        
        if( $total == 1 ){
            
            if( ! $return = $this->db->where($this->primary_key, '=', $args[0])->row() )
                return false;
            
            foreach( get_object_vars($return) as $key => $val )
                $this->$key = $val;
            
            return $return;
        }
        
        if( $total > 1 )
            return $this->db->where($this->primary_key, 'IN', $args)->results();
        
        
        return $this->db->results();
    }
    
    public function delete(){
        
        if( ! call_user_func_array(array($this, 'find'), func_get_args()) )
            return false;
        
        $primary_key = $this->primary_key;
        
        return $this->db->delete($this->table, array($this->primary_key => $this->$primary_key)); 
    }
    
    public function where($sql_condition = null){
        
    }
    
    public function order(){
        
    }
    
    public function select(){
        
    }
    
    public function limit(){
        
    }
    
    public function offset(){
        
    }
    
    public function group(){
        
    }
    
    public function __call($name, $arguments){
        
        $this->db->select()->from($this->table);
        
        if($name == 'first')
            return $this->db->order_by($this->primary_key, 'ASC')->limit(1)->row();
        
        if($name == 'last')
            return $this->db->order_by($this->primary_key, 'DESC')->limit(1)->row();
            
    }

}