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
    protected $primary_key = 'id';
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
        
        return $this->db->insert( $this->table, $this->get_fields() );
    }
    
    /**
     * Get records from db
     *
     * @param array $where
     * @param int $limit
     * @return object if true else false
     */
    public function find( $args = 1 ){
        
        // Retrieving a Single Object by Primary Key
        if( is_numeric($args) )
            return $this->db->get_row( $this->table, array($this->primary_key => $args) );
        
        return $this->db->get_results( $this->table, $where );
    }
    
    /**
     * Get one record from db
     *
     * @param array $where
     * @return object if true else false
     */
    public function find_one( $where = array(), $fields = array() ){
        
        return $this->db->get_row( $this->table, $where, $fields );
    }
    
    public function __call($name, $arguments){
        
        if($name == 'first')
            return $this->db->get_row( $this->table, array(), array(), 1 );
            
    }

}