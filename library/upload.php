<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada Upload API.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */

class Library_upload {
    
    /**
     * @var array   EN: Define the $_FILES varible.
     *              ID: Defenisikan variable penyimpan nilai dari $_FILES.
     */
    public $file;
    
    /**
     *@var object   EN: Error message container.
     *              ID: Penampung pesan error.
     */
    public $error;
    
    /**
     * @var array   EN: Initiate the error mesages.
     *              ID: Set pesan-pesan error.
     */
    public $error_messages = array();
    
    /**
     * @var string  EN: Folder location.
     *              ID: Lokasi folder.
     */
    public $folder_location = '';
    
    /**
     * @var string  EN: Define file name manually.
     *              ID: Tentukan nama file secara manual.
     */
    public $set_file_name = '';
    
    /**
     * @var boolean EN: Need auto rename the file?
     *              ID: Setting untuk menamai file secara otomatis.
     */
    public $auto_rename = false;
    
    /**
     * @var boolean EN: Remove any space in file name.
     *              ID: Hapus spasi pada nama file.
     */
    public $strip_spaces = true;
    
    /**
     * @var integer EN: Define maximum file size.
     *              ID: Tentukan besar maksimum ukuran file.
     */
    public $maximum_size = 0;
    
    /**
     * @var boolean EN: Create subdirectory automaticly. The format is "destination_folder/year/month".
     *              ID: Membuat sub-folder secara otomatis. Formatnya "folder_tujuan/tahun/bulan".
     */
    public $auto_create_folder = false;
    
    /**
     * @var array   EN: Collect the file information: name, extension, path etc...
     *              ID: Kumpulan informasi file seperti: nama, ekstensi, lokasi dll...
     */
    public $get_file_info = array();
    
    /**
     * @var string  EN: Any files that are allowed.
     *              ID: File-file apa saja yang diinginkan. contoh jpg|png|gif
     */
    public $permitted_file_type = '';
    
    /**
     * @var object  EN: Instance for Image modifier class (Library_image).
     */
    public $image;
    
    /**
     * @var string  EN: Option to edit image base on Library_image class. The option is resize | crop | resize_crop
     */
    public $edit_image = '';
    
    
    /**
     * EN: Class constructor.
     *
     * @return void
     */
    function __construct(){
        
        $this->init_error_messages();
    }
    
    /**
     * EN: Do the Processing upload.
     * ID: Lakukan proses ungguh.
     *
     * @param array $_FILES variable
     * @return boolean
     */
    public function now($file){
        
        $this->file = $file;
        
        $this->error_handler();
        
        if( ! empty($this->error) )
            return false;
        
        if( ! $this->upload() )
            return false;
        
        if( ! empty($this->edit_image) ) {
            
            // EN: Initiate Library_image class
            $this->image            = new Library_image();
            // EN: See Library_image class line 65
            $this->image->folder    = $this->folder_location;
            
            // EN: Assign each config for Library_image class
            foreach($this->edit_image as $key => $val)
                $this->image->$key = $val;
            
            if( ! $this->image->edit($this->get_file_info['name']) ) {
               $this->set_error_mesage(14);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * EN: List of error messages.
     * ID: List pesan-pesan error.
     *
     * @return void
     */
    private function init_error_messages(){
        
        $this->error_messages = array (
            1 => 'File upload failed due to unknown error.',
            2 => 'No folder located. Please define the folder location.',
            3 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            4 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            5 => 'The uploaded file was only partially uploaded.',
            6 => 'No file was uploaded.',
            7 => 'Missing a temporary folder.',
            8 => 'Failed to write file to disk.',
            9 => 'File upload stopped by extension.',
            10 => 'Folder you\'ve defined does not exist.',
            11 => 'Can\t create new folder in your defined folder.',
            12 => 'Uploaded file not permitted.',
            13 => 'The uploaded file exceeds the maximum size.',
            14 => 'File uploaded, but editing image has failed with the following error(s): ',
            15 => 'Folder you specified not writalbe.',
        ); 
    }
    
    /**
     * EN: Set the error mesage.
     *
     * @param integer
     * @return void
     */
    private function set_error_mesage($code){
        
        $image_error        = ($code == 14 && isset($this->image->error_messages)) ? implode(', ', $this->image->error_messages) : null;
        $handler            = new stdClass;
        $handler->code      = $code;
        $handler->message   = $this->error_messages[$code] . $image_error;
        $this->error        = $handler;
    }
    
    /**
     * EN: Error checker before uploading proceed.
     * ID: Pastikan tidak ada error sebelum proses ungguh dilakukan.
     *
     * @return void
     */
    private function error_handler(){
        
        /**
         * EN: Check is folder destionation has set.
         * ID: Pastikan folder tujan telah ditentukan.
         */
        if( empty($this->folder_location) ) {
            $this->set_error_mesage(2);
            return false;
        }
        
        /**
         * EN: Does it folder exist?
         * ID: Apakah folder tersebut ada?
         */
        if( ! is_dir($this->folder_location) ) {
            $this->set_error_mesage(10);
            return false;
        }
        
        /**
         * EN: Does it folder writable?
         * ID: Apakah folder tersebut bisa untuk tulis/hapus?
         */
        if( ! is_writable($this->folder_location) ) {
            $this->set_error_mesage(15);
            return false;
        }
        
        /**
         * EN: Make sure this file are permitted.
         * ID: Memastikan bahwa file ini diijinkan.
         */
        if( ! empty($this->permitted_file_type) ) {
            if ( ! preg_match( '!\.(' . $this->permitted_file_type . ')$!i', $this->file['name'] ) ) {
                $this->set_error_mesage(12);
                return false;
            }
        }
        
        /**
         * EN: Make sure the file size not more then user defined.
         * ID: Memastikan ukuran file tidak lebih besar dari yang ditetapkan.
         */
        if( $this->maximum_size > 0 && $this->file['size'] > $this->maximum_size) {
            $this->set_error_mesage(13);
            return false;
        }
        
        if($this->auto_create_folder) {
            
            $folder         = $this->folder_location . '/' . date('Y') . date('m');
            $year_folder    = $this->folder_location . '/' . date('Y');
            $month_folder   = $year_folder . '/' . date('m');
            
            if( ! is_dir($folder) ) {
                
                /**
                 * EN: Create year folder if it not exits.
                 * ID: Buat folder tahun jika belum ada.
                 */
                if( ! is_dir($year_folder) ) {
                    if( ! mkdir($year_folder, 0777)) {
                        $this->set_error_mesage(11);
                        return false;
                    }
                }
                
                /**
                 * EN: Create month folder if it not exits.
                 * ID: Buat folder bulan jika belum ada.
                 */
                if( ! is_dir($month_folder) ) {
                    if( ! mkdir($month_folder, 0777)) {
                        $this->set_error_mesage(11);
                        return false;
                    }
                }
                
                $this->folder_location = $month_folder;
            }
            else {
                $this->folder_location = $folder;
            }
        }
        
        /**
        * EN: Checking error in uploading proccess.
        * ID: Memeriksa error pada proses unggah.
        */
        if($this->file['error']){
            
            switch($this->file['error']){
                
                case UPLOAD_ERR_INI_SIZE:
                    $this->set_error_mesage(3);
                    return false;
                case UPLOAD_ERR_FORM_SIZE:
                    $this->set_error_mesage(4);
                    return false;
                case UPLOAD_ERR_PARTIAL:
                    $this->set_error_mesage(5);
                    return false;
                case UPLOAD_ERR_NO_FILE:
                    $this->set_error_mesage(6);
                    return false;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $this->set_error_mesage(7);
                    return false;
                case UPLOAD_ERR_CANT_WRITE:
                    $this->set_error_mesage(8);
                    return false;
                case UPLOAD_ERR_EXTENSION:
                    $this->set_error_mesage(9);
                    return false;
                default:
                    $this->set_error_mesage(1);
            }
        }
        
    }
    
    static function get_file_extension($file){
        
        return strtolower(end(explode('.', $file)));
    }
    
    /**
     * EN: Do uploading.
     * ID: Lakukan ungguh.
     *
     * @return void
     */
    private function upload(){
        
        $file_extension = $this->get_file_extension($this->file['name']);
        
        if($this->auto_rename)
            $name = time() . rand() . '.' . $file_extension;
        elseif( ! empty($this->set_file_name) )
            $name = $this->set_file_name . '.' .$file_extension;
        else
            $name = $this->file['name'];
        
        // EN: Remove space in file name.
        if( $this->strip_spaces)
            $name = str_replace(' ', '_', $name);
        
        // EN: Save file extension.
        $this->get_file_info['extension']   = $file_extension;
        // EN: Save file name.
        $this->get_file_info['name']        = $name;
        // EN: Save folder location.
        $this->get_file_info['folder']      = $this->folder_location;
        // EN: Save mime type.
        $mime = $this->get_mime_types($name);
        $this->get_file_info['mime']        = $mime['type'];
        
	$file_path  = $this->folder_location . '/' . $name;
        
        if( move_uploaded_file($this->file['tmp_name'], $file_path) ) {
            return true;
        }
        else {
            $this->set_error_mesage(1);
            return false;
        }
    }
    
    /**
     * EN: Define file mime type. Original from Wordpress 3.0 get_allowed_mime_types() function in wp-includes/functions.php
     * ID: List file mime type.
     *
     * @param string
     * @return boolean|array
     */
    static function get_mime_types($file_name = '') {
        
        if( empty($file_name) )
            return false;
        
        $mimes = array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'bmp' => 'image/bmp',
            'tif|tiff' => 'image/tiff',
            'ico' => 'image/x-icon',
            'asf|asx|wax|wmv|wmx' => 'video/asf',
            'avi' => 'video/avi',
            'divx' => 'video/divx',
            'flv' => 'video/x-flv',
            'mov|qt' => 'video/quicktime',
            'mpeg|mpg|mpe' => 'video/mpeg',
            'txt|asc|c|cc|h' => 'text/plain',
            'csv' => 'text/csv',
            'tsv' => 'text/tab-separated-values',
            'rtx' => 'text/richtext',
            'css' => 'text/css',
            'htm|html' => 'text/html',
            'mp3|m4a|m4b' => 'audio/mpeg',
            'mp4|m4v' => 'video/mp4',
            'ra|ram' => 'audio/x-realaudio',
            'wav' => 'audio/wav',
            'ogg|oga' => 'audio/ogg',
            'ogv' => 'video/ogg',
            'mid|midi' => 'audio/midi',
            'wma' => 'audio/wma',
            'mka' => 'audio/x-matroska',
            'mkv' => 'video/x-matroska',
            'rtf' => 'application/rtf',
            'js' => 'application/javascript',
            'pdf' => 'application/pdf',
            'doc|docx' => 'application/msword',
            'pot|pps|ppt|pptx|ppam|pptm|sldm|ppsm|potm' => 'application/vnd.ms-powerpoint',
            'wri' => 'application/vnd.ms-write',
            'xla|xls|xlsx|xlt|xlw|xlam|xlsb|xlsm|xltm' => 'application/vnd.ms-excel',
            'mdb' => 'application/vnd.ms-access',
            'mpp' => 'application/vnd.ms-project',
            'docm|dotm' => 'application/vnd.ms-word',
            'pptx|sldx|ppsx|potx' => 'application/vnd.openxmlformats-officedocument.presentationml',
            'xlsx|xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml',
            'docx|dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml',
            'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',
            'swf' => 'application/x-shockwave-flash',
            'class' => 'application/java',
            'tar' => 'application/x-tar',
            'zip' => 'application/zip',
            'gz|gzip' => 'application/x-gzip',
            'exe' => 'application/x-msdownload',
            // openoffice formats
            'odt' => 'application/vnd.oasis.opendocument.text',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'odg' => 'application/vnd.oasis.opendocument.graphics',
            'odc' => 'application/vnd.oasis.opendocument.chart',
            'odb' => 'application/vnd.oasis.opendocument.database',
            'odf' => 'application/vnd.oasis.opendocument.formula',
            // wordperfect formats
            'wp|wpd' => 'application/wordperfect',
            // php formats
            'php|php4|php3|phtml' => 'application/x-httpd-php',
	    'phps' => 'application/x-httpd-php-source',
        );
        
        foreach ( $mimes as $ext_preg => $mime_match ) {
            $ext_preg = '!\.(' . $ext_preg . ')$!i';
            if ( preg_match( $ext_preg, $file_name, $ext_matches ) ) {
                $file['type'] = $mime_match;
                $file['ext'] = $ext_matches[1];
                break;
            }
	}
        
        return $file;
    }
} //End Upload Class