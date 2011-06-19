<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada MongoDB API.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.2
 */

/**
 * ID: Pastikan ektensi Mongo telah terinstall
 * EN: Makesure Memcache extension is enabled
 */
if( ! extension_loaded('mongo') )
    Library_error::_500('Mongo extension that required by Driver_mongodb is not available.');

class Drivers_database_mongodb extends Mongo {
    
    private $database;
    private $db_config;
    private $connection;
    private $criteria = array();
    
    public function __construct( $config_instance, $connection_name ){
        
        $this->db_config = $config_instance;
        $this->connection = $connection_name;
        
        /**
         * EN: This is the mongodb connection option. Eg: array('replicaSet' => true, 'connect' => false)
         */
        $connection_options = (array) $this->db_config->options;
        
        parent::__construct($this->db_config->host, $connection_options);
    }
    
    public function collection($collection){
        
        $database = $this->db_config->database;
        $db = $this->$database;
        return $db->$collection;
    }
    
    /**
     * EN: Wrap results from mongo output into object or array.
     *
     * @param array $cursor The array data given from Mongodb
     * @param string $output The output type: object | array
     * @return boolean | object | array
     */
    public function cursor_results($cursor, $output = 'object'){
        
        if( ! $cursor )
            return false;
        
        /**
         * ID: Jika outputnya ingin berbentuk array, maka
         *      lakukan proses di bawah. Jika tidak abaikan.
         */
        if( $output == 'array'){
            
            foreach ($cursor as $value)
                $return[] = $value;
            
            return $return;
        }
        
        foreach ($cursor as $value)
            $return[] = (object) Library_tools::array_to_object($value);
            
        return $return;
        
    }
    
    /**
     * EN: Convert string time into mongo date
     *
     * @param string $str
     */
    public function date($str){
        
        return new MongoDate(strtotime($str));
    }
    
    /**
     * EN: Convert a string unique identifier into MongoId object.
     *
     * @param string $_id Mongodb string id
     * @return object
     */
    public function _id($_id = null){
        
        return new MongoId($_id);
    }
    
    public function select(){
        
	$this->documents = func_get_args();
	
        if( ! empty($this->documents) )
	    $this->documents = array_fill_keys($this->documents, 1);
        
        return $this;
    }
    
    public function from($collection_name){
	
	$this->collection_name = $collection_name;
	
	return $this;
    }
    
    public function where($document, $operator = null, $value = null, $separator = false){
        
        if( is_array($document) )
            $this->criteria = $document;
        
        if($operator == '=')
            $this->criteria[$document] = $value;
	
	if($operator == '>')
            $this->criteria[$document]['$gt'] = $value;
	
	if($operator == '<')
            $this->criteria[$document]['$lt'] = $value;
	
	if($operator == '>=')
            $this->criteria[$document]['$gte'] = $value;
	
	if($operator == '<=')
            $this->criteria[$document]['$lte'] = $value;
        
        return $this;
    }
    
    public function find_all(){
        
	$value = $this->collection($this->collection_name)->find( $this->criteria, $this->documents );
	$this->criteria = $this->documents = array();
	
	if( ! empty($value) )
	    return $this->cursor_results($value);
	
	return false;
    }
    
    public function find_one(){
        
	$value = $this->collection($this->collection_name)->findOne( $this->criteria, $this->documents );
	$this->criteria = $this->documents = array();
	
	if( ! empty($value) )
	    return (object) $value;
	
	return false;
    }
    
    public function insert($collection, $data = array()) {
        
        return $this->collection($collection)->insert($data); 
    }
    
    public function update($collection, $data, $criteria = null){
	
	$this->where($criteria);
	$value = $this->collection($collection)->update( $this->criteria, array('$set' => $data) );
	$this->criteria = array();
	
	return $value;
    }
    
    public function delete( $collection, $criteria = null ){
        
	$this->where($criteria);
	$value = $this->collection($collection)->remove( $this->criteria );
	$this->criteria = array();
	
	return $value;
    }
    
} // End Driver_mysql Class