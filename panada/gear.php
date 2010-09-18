<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada
 *
 * Light and simple PHP 5 base Framework.
 *
 * @package	Panada
 * @category    Panada core system
 * @author	Iskandar Soesman
 * @copyright	Copyright (c) 2010, Iskandar Soesman.
 * @license	http://www.opensource.org/licenses/bsd-license.php
 * @link	http://www.kandar.info/panada/
 * @since	Version 0.1
 */




/**
 * EN: Start count time execution.
 * ID: Mulai menghitung waktu eksekusi. Silahkan uncomment method 'Library_time_execution::start()' untuk menghitung waktu eksekusi.
 */
//Library_time_execution::start();



/**
 * Panada cache class and object.
 *
 * EN: Store class variable name and its object for temporary.
 * ID: Tempat penyimpanan sementara nama variable class dan object-nya masing-masing.
 * 
 * @package	Panada
 * @category	Main System
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */
class Panada_cacher {
    
    static private $instance;
    public $class_object = array();
    public $defined_objet = null;
    
    public static function instance(){
        
        if( ! self::$instance ) {
            $cache = new Panada_cacher();
            self::$instance = $cache;
            return $cache;
        }
        else {
            return self::$instance;
        }
    }
}



/**
 * EN: Load url class to get info: class name, method, request etc.. base on url path.
 * ID: Load class uri untuk mendapatkan informasi nama class, method, requst dll berdasarkan path url.
*/
$pan_uri    = new Library_uri();
$class      = 'Controller_'.$pan_uri->get_class();
$method     = $pan_uri->get_method();
$request    = $pan_uri->get_requests();



/**
 * Class's file loader
 * 
 * EN:	Load the class's file automaticly.
 * ID:	Fungis untuk me-load file class secara otomatis.
 * 
 * @access	public
 * @param	string
 * @return	void
 * @since	version 0.1
 */
function __autoload($class_name) {
    
    //EN: Strip class name from folder prefix.
    $var_name	= Panada::var_name($class_name);
    
    //EN: Explode the class name base on '_' to get folder name.
    $prefix	= explode('_', $class_name);
    
    //EN: Folder where file located.
    $folder     = strtolower($prefix[0]);
    
    //EN: Reconstruct file name and folder location.
    $file	= $folder .'/'. $var_name;
    
    
    //EN: Are we trying to load a library file?
    if( $folder == 'library' ){
	
        //EN: Does the file exist in main library folder?
	if( file_exists( $libsys_file = GEAR . $file . '.php' ) ) {
	    
	    $class_file = $libsys_file;
	}
        //EN: Or meybe in library application?
	else if( file_exists( $apps_file = APPLICATION . $file . '.php' ) ){
	    
	    $class_file = $apps_file;
	}
        //EN: Oops! Panada Can't find anywhere, so stop the execution!
	else{
	    
	    Library_error::costume(500, '<h2>Error: No '.$var_name.' file in library folder.</h2>');
	}
        
    }
    else{
	
	$class_file = APPLICATION . $file .'.php';
    }
    
    include_once $class_file;
    
    /**
     * EN: Exclude some class from caching.
     * ID: Abaikan beberap class yang tidak perlu di-cache.
     */
    if( strtolower($class_name) == 'library_uri' || strtolower($class_name) == 'library_error' || strtolower($class_name) == 'library_time_execution' || strtolower($class_name) == 'controller_'.$var_name )
        return;
    
    $panada_cacher  = Panada_cacher::instance();
    $Panada         = Panada::instance();
    
    if( ! isset($Panada->$var_name) ) {
        $Panada->$var_name = new $class_name;
        $panada_cacher->class_object[$var_name] = $Panada->$var_name;
    }
    
    $panada_cacher->defined_objet = array_keys(get_object_vars($Panada));
    
}



/**
 * Panada super object manager
 *
 * EN:  Adding or removing object dinamicly.
 * ID:	Berfungsi untuk menambah atau mengurangi object
 *	secara dinamis.
 *
 * @package	Panda
 * @category	Main System
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */
class Panada {
    
    private static $instance;
    
    public function __construct(){
        
        self::$instance = $this;
        $this->config   = Library_config::instance();
        $this->base_url	= $this->config->base_url;
	$this->auto_loader();
    }
    
    public static function instance(){
        return self::$instance;
    }
    
    public function location($location = ''){
	return $this->base_url . $this->config->index_file . $location;
    }
    
    public function redirect($location = ''){
        
        $location = ( empty($location) ) ? $this->location() : $location;
        
        if ( substr($location,0,4) != 'http' )
            $location = $this->location() . $location;
        
        header('Location:' . $location);
        exit;
    }
    
    /**
     * Object assigner
     *
     * EN: Adding sub-object to each existing object.
     * ID: Menambahakan sub-object ke masing-masing object yang ada.
     * Hal ini berlaku pada semua class yang dipanggil kecuali class config (karena class ini hanya memberikan data dan tidak melakukan proses)
     * dan class yang sudah dinyatakan tidak perlu dicache. Lihat line 127.
     *
     * @access public
     * @return void
     */
    public static function assigner(){
        
        $Panada         = Panada::instance();
        $panada_cacher  = Panada_cacher::instance();
        
	if( isset($panada_cacher->defined_objet) )
	    foreach ($panada_cacher->defined_objet as $key) {
		if($key != 'config'){
		    foreach ($panada_cacher->class_object as $class => $object)
			if( $class !== $key)
			    if( is_object($Panada->$key) )
				$Panada->$key->$class = $object;
		}
	    }
    }
    
    /**
     * Variable name creator
     *
     * EN: Remove folder name prefix within the file name so it become a real file name.
     * ID: Menghapus prefix nama folder yang ada pada bagian awal nama file untuk menjadi nama file sebenarnya.
     *
     * @access public
     * @param string
     * @return string
     */
    public static function var_name($class_name){
	return strtolower(str_ireplace(array('library_', 'model_', 'controller_'), '', $class_name));
    }
    
    /**
     * Auto loader
     *
     * EN: Load defined classes.
     * ID: Me-load class-class yang telah didefenisikan.
     *
     * @access public
     * @return void
     */
    public function auto_loader(){
        
        if( ! empty($this->config->auto_loader) ) {
            
            $auto_loader = (array) $this->config->auto_loader;
            
            foreach( $auto_loader as $class_name){
		$var = self::var_name($class_name);
                $this->$var = new $class_name();
            }
        }
    }
    
    /**
    * Load View
    *
    * EN: Load the "view" file.
    * ID: Berfungsi untuk me-load file "view".
    *
    * @access	public
    * @param	string
    * @param	array
    * @return	void
    */
    public function view($view, $data = array()){
        
        if( ! file_exists( $path = APPLICATION . 'view/' . $view . '.php') )
            Library_error::costume(500, '<h2>Error: No ' . $view . ' file in view folder.</h2>');
        
	if( ! empty($data) )
            $this->view = $data;
        
        if( ! empty($this->view ) )
             extract( $this->view, EXTR_SKIP );
        
        include_once $path;
    }
}



/**
 * Load controller
 *
 * EN: Load the default controller called 'home'. Make sure the file exist. If not, stop the execution.
 * ID: Controller default bernama 'home'. Pastikan file controller tersedia. Jika tidak, stop eksekusi.
*/
if ( ! file_exists( APPLICATION . 'controller/' . $pan_uri->get_class() . '.php') ){
    
    /**
     * EN: Meybe it alias url page?? (eg: www.website.com/username) if yes lets redefine controller, method and request.
     * ID: Apakah alias url aktif? jika ya defenisikan ulang controller, method dan request.
     */
    
    $config = Library_config::instance();
    
    if( count(get_object_vars($config->alias_controller)) > 0 ){
        
        $request = array($pan_uri->break_uri_string(1));
        
        if( $_request = $pan_uri->get_requests(2) )
            $request = array_merge($request, $_request);
        
        /**
         * EN: Filtering/validating request before send to controller.
         * ID: Lakukan fiter/validasi pada request parameter sebelum dikirimkan ke controller.
         */
        if( $config->request_filter_type != false )
	    $request = filter_var_array($request, $config->request_filter_type);
        
        $_class = array_keys(get_object_vars($config->alias_controller));
        $method = $config->alias_controller->$_class[0];
        $class  = 'Controller_' . $_class[0];
        
        /**
         * EN: Check once again does the file's controller exist?
         * ID: Memastikan kembali apakah file untuk Alias Contorller tersedia.
         */
        if ( ! file_exists( APPLICATION . 'controller/' . $_class[0] . '.php') )
            Library_error::_404();
    }
    else {
        Library_error::_404();
    }
}


/**
 * EN: Oke the file exits, now create class's instance.
 * ID: File telah siap, waktunya membuat instance class.
 */
$Panada = new $class;



/**
 * EN:	Assigning each cached object into defined object recursively.
 * ID:	Daftarkan object-object yang telah dicache ke object-object baru.
 */
Panada::assigner();



/**
 * ID:	Method default bernama 'index'
 *	Pastikan method tersedia/atau bisa dipanggil.
 *	Jika tidak ada, apakah method alias tersedia?
 *	Jika tidak stop eksekusi.
*/
if( ! method_exists($Panada, $method) ){
    
    /**
     * ID: Atur ulang struktur variable method dan request.
     */
    $request = array($method);
    $method = $Panada->config->alias_method;
    
    if( ! method_exists($Panada, $method) )
        Library_error::_404();
}


/**
 * EN: All required component has ready, its time to show the page.
 * ID: Semua komponen telah siap, saatnya menampilkan halaman.
 */
if($request)
    call_user_func_array(array($Panada, $method), $request);
else
    $Panada->$method();

/**
 * EN:	For debugging purpse.
 * ID:	Untuk 'nge'-debug.
 */
//print_r($Panada);

/**
 * EN: Uncomment below to count time execution.
 * ID: Silahkan uncomment method di bawah untuk menghitung waktu eksekusi.
 */
//Library_time_execution::stop();

/**
 * EN: Close db connection if present
 * ID: Tutup koneksi databse jika ada.
 */
if( isset($Panada->db) )
    mysql_close($Panada->db->link);

/**
 * EN: End of the cicle, lets clear the memory. Do we need this??
 * ID: Jika diperlukan untuk membersihkan memory.
 * unset($Panada);
 */