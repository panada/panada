<?php defined('THISPATH') or die('Tidak diperkenankan mengakses file secara langsung.');
/**
 * Panada
 *
 * Light and simple PHP 5 base Framework.
 *
 * @package	Panada
 * @category    Panada core system
 * @author	Kandar
 * @copyright	Copyright (c) 2010, Iskandar Soesman.
 * @license	http://www.opensource.org/licenses/bsd-license.php
 * @link	http://www.kandar.info/panada/
 * @since	Version 0.1
 * @filesource
 */

// -------------------------------------------------------------------



/**
 * EN: Count the time execution.
 *
function timer_start() {
    global $timestart;
    $mtime = explode(' ', microtime() );
    $mtime = $mtime[1] + $mtime[0];
    $timestart = $mtime;
    return true;
}
function timer_stop($display = 0, $precision = 3) { //if called like timer_stop(1), will echo $timetotal
    global $timestart, $timeend;
    $mtime = microtime();
    $mtime = explode(' ',$mtime);
    $mtime = $mtime[1] + $mtime[0];
    $timeend = $mtime;
    $timetotal = $timeend-$timestart;
    $r = ( function_exists('number_format_i18n') ) ? number_format_i18n($timetotal, $precision) : number_format($timetotal, $precision);
    if ( $display )
	    echo $r;
    return $r;
}

timer_start();
*/



/**
 * Load configuration file
*/
require_once APPLICATION . 'config.php';



/**
 * Panada cache class and object.
 *
 * EN: Store class variable name and its object for temporary.
 * ID: Tempat penyimpanan sementara nama variable class dan object-nya masing-masing.
 * 
 * @package	Panada
 * @category	Main System
 * @author	Kandar
 * @since	Version 0.1
 */
class Panada_cacher {
    
    var $class_object = array();
    var $defined_objet = null;
    
    /**
     * cache debugger
     *
     * EN: For debugging purepose, get all cached data.
     * ID: Mendapatkan data apa saja yang disimpan dalam cache untuk tujuan debug.
     *
     * @access public
     * @return array
     */
    function show_cache(){
        
        return array('class_object' =>$this->class_object, 'defined_objet' => $this->defined_objet);
    }
}



/**
 * EN:	Initiate Panada class and object.
 * ID:	Memualai class Panada_cacher.
*/
$panada_cacher = new Panada_cacher();



/**
 * EN: Load url class to get info: class name, method, request etc.. base on url path.
 * ID: Load class uri untuk mendapatkan informasi nama class, method, requst dll berdasarkan path url.
*/
$pan_uri    = new library_uri();
$class      = 'controller_'.$pan_uri->fetch_class();
$method     = $pan_uri->fetch_method();
$request    = $pan_uri->fetch_request();



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
    //EN: Convert undersore into slash.
    $file	= str_replace('_', '/', $class_name);
    //EN: Explode the class name base on '_' to get folder name.
    $prefix	= explode('_', $class_name);
    
    
    //EN: Are we trying to load a library file?
    if( $prefix[0] == 'library' ){
	
        //EN: Does the file exist in main library folder?
	if( file_exists( $libsys_file = THISPATH . GEAR . $file . '.php' ) ) {
	    
	    $class_file = $libsys_file;
	}
        //EN: Or meybe in library application?
	else if( file_exists( $apps_file = APPLICATION . $file . '.php' ) ){
	    
	    $class_file = $apps_file;
	}
        //EN: Oops! Panada Can't find anywhere, so stop the execution!
	else{
	    
	    library_error::costume(500, '<h2>Error: No '.$var_name.' file in library folder.</h2>');
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
    if( $class_name == 'library_uri' || $class_name == 'library_error' || $class_name == 'controller_'.$var_name )
        return;
    
    $panada_cacher  = $GLOBALS['panada_cacher'];
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
 * @author	kandar
 * @since	Version 0.1
 */
class Panada {
    
    private static $instance;
    
    function __construct(){	
        self::$instance = $this;
        
        $this->base_url	= $GLOBALS['CONFIG']['base_url'];
	$this->auto_loader();
    }
    
    public static function instance(){
        return self::$instance;
    }
    
    /**
     * Object assigner
     *
     * EN: Adding sub-object to each existing object.
     * ID: Menambahakan sub-object ke masing-masing object yang ada.
     *
     * @access public
     * @return void
     */
    public static function assigner(){
        
        $Panada         = Panada::instance();
        $panada_cacher  = $GLOBALS['panada_cacher'];
        
	if( isset($panada_cacher->defined_objet) )
	    foreach ($panada_cacher->defined_objet as $key) {
		foreach ($panada_cacher->class_object as $class => $object)
		    if( $class !== $key)
			if( is_object($Panada->$key) )
			    $Panada->$key->$class = $object;
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
	return str_replace(array('library_', 'model_', 'controller_'),'',$class_name);
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
    function auto_loader(){
        
        if( ! empty($GLOBALS['CONFIG']['auto_loader']) )
            foreach( $GLOBALS['CONFIG']['auto_loader'] as $class_name){
		$var = self::var_name($class_name);
                $this->$var = new $class_name();
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
    function view($view, $data = array()){
        
        if( ! file_exists( $path = APPLICATION . 'view/' . $view . '.php') )
            library_error::costume(500, '<h2>Error: No ' . $view . ' file in view folder.</h2>');
        
	if( ! empty($data) ){
            $this->view = $data;
	    extract( $this->view, EXTR_SKIP );
	}
        
        include_once $path;
    }
}



/**
 * Load controller
 *
 * EN: Load the default controller called 'welcome'. Make sure the file exist. If not, stop the execution.
 * ID: Controller default bernama 'welcome'. Pastikan file controller tersedia. Jika tidak, stop eksekusi.
*/
if ( ! file_exists( APPLICATION . 'controller/' . $pan_uri->fetch_class() . '.php') ){
    
    /**
     * EN: Meybe it short url page?? (eg: www.website.com/username) if yes lets redefine controller, method and request.
     * ID: Apakah short url aktif? jika ya defenisikan ulang controller, method dan request.
     */
    if( ! empty($CONFIG['short_url']) ){
        
        $request = array(Panada::var_name($class));
        
        if( $_request = $pan_uri->fetch_request(2) )
            $request = array_merge($request, $_request);
        
        $_class = array_keys($CONFIG['short_url']);
        $method = $CONFIG['short_url'][$_class[0]];
        $class  = 'controller_' . $_class[0];
        
        /**
         * EN: Check once again does the file's controller exist?
         * ID: Memastikan kembali apakah file untuk contorller short_url tersedia.
         */
        if ( ! file_exists( APPLICATION . 'controller/' . $_class[0] . '.php') )
            library_error::_404();
    }
    else {
        library_error::_404();
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
 * Load method
 * 
 * ID:	Method default bernama 'index'
 *	Pastikan method tersedia/atau bisa dipanggil. Jika tidak stop eksekusi.
*/
if( ! method_exists($Panada,$method) )
    library_error::_404();


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
 * ID: Silahkan uncomment fungsi di bawah untuk menghitung waktu eksekusi.
 */
//timer_stop(1);

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