<?php
/**
 * Panada MongoDB API.
 *
 * @package	Driver
 * @subpackage	Database
 * @author	Iskandar Soesman
 * @since	Version 0.2
 */
namespace Drivers\Database;
use Resources\Tools as Tools,
    Resources\RunException as RunException;

class Mongodb extends \Mongo {
    
    private
        $database,
        $config,
        $connection,
        $criteria = array();
    
    public function __construct( $config, $connectionName ){
        
        /**
        * Makesure Mongo extension is enabled
        */
       if( ! extension_loaded('mongo') )
           throw new RunException('Mongo extension that required by Mongodb Driver is not available.');
        
        $this->config = $config;
        $this->connection = $connectionName;
        
        /**
         * $this->config['options'] is the mongodb connection option. Eg: array('replicaSet' => true, 'connect' => false)
         */
        parent::__construct($this->config['host'], $this->config['options']);
    }
    
    public function collection($collection){
        
        $database = $this->config['database'];
        $db = $this->$database;
        return $db->$collection;
    }
    
    /**
     * Wrap results from mongo output into object or array.
     *
     * @param array $cursor The array data given from Mongodb
     * @param string $output The output type: object | array
     * @return boolean | object | array
     */
    public function cursorResults($cursor, $output = 'object'){
        
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
            $return[] = (object) Tools::arrayToObject($value);
            
        return $return;
        
    }
    
    /**
     * Convert string time into mongo date
     *
     * @param string $str
     */
    public function date($str){
        
        return new \MongoDate(strtotime($str));
    }
    
    /**
     * Convert a string unique identifier into MongoId object.
     *
     * @param string $_id Mongodb string id
     * @return object
     */
    public function _id($_id = null){
        
        return new \MongoId($_id);
    }
    
    /**
     * Method for select field(s) in a collection.
     *
     * @return object
     */
    public function select(){
        
	$this->documents = func_get_args();
	
        if( ! empty($this->documents) )
	    $this->documents = array_fill_keys($this->documents, 1);
        
        return $this;
    }
    
    /**
     * Get the collection name.
     *
     * @return object
     */
    public function from($collection_name){
	
	$this->collection_name = $collection_name;
	
	return $this;
    }
    
    /**
     * Build criteria condition.
     * Translate SQL like operator into mongo string operator.
     *
     * @param string | array Document field
     * @param string SQL operator
     * @param string Vlaue to compare
     * @param string Separator for more then one condition
     * @return object
     */
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
    
    /**
     * Find more then one document
     *
     * @return mix
     */
    public function getAll(){
        
	$value = $this->collection($this->collection_name)->find( $this->criteria, $this->documents );
	$this->criteria = $this->documents = array();
	
	if( ! empty($value) )
	    return $this->cursorResults($value);
	
	return false;
    }
    
    /**
     * Find a document
     *
     * @return mix
     */
    public function getOne(){
        
	$value = $this->collection($this->collection_name)->findOne( $this->criteria, $this->documents );
	$this->criteria = $this->documents = array();
	
	if( ! empty($value) )
	    return Tools::arrayToObject($value);
	
	return false;
    }
    
    /**
     * Insert new document
     *
     * @param string Collection name
     * @param array Data to insert
     * @return bool
     */
    public function insert($collection, $data = array()) {
        
        return $this->collection($collection)->insert($data); 
    }
    
    
    /**
     * Update document
     *
     * @param string Collection name
     * @param array Data to update
     * @param string SQL like criteria
     * @return bool
     */
    public function update($collection, $data, $criteria = null){
	
	$this->where($criteria);
	$value = $this->collection($collection)->update( $this->criteria, array('$set' => $data) );
	$this->criteria = array();
	
	return $value;
    }
    
    /**
     * Delete a document
     *
     * @param string Collection name
     * param string SQL like criteria
     * @return bool
     */
    public function delete( $collection, $criteria = null ){
        
	if( ! empty($criteria) )
	    $this->where($criteria);
	
	$value = $this->collection($collection)->remove( $this->criteria );
	$this->criteria = array();
	
	return $value;
    }
    
}