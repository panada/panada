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
    Library_error::_500('Mongo extension that required by Library_mongodb is not available.');

class Library_mongodb extends Mongo {
    
    public $database;
    
    public function __construct( $connection = 'default' ){
        
        $this->config = new Library_config();
        
        $this->database = $this->config->mongodb->$connection->database;
        $host = $this->config->mongodb->$connection->host;
        
        /**
         * EN: This is the mongodb connection option. Eg: array('replicaSet' => true, 'connect' => false)
         */
        $connection_options = array();
        
        parent::__construct($host, $connection_options);
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
}
