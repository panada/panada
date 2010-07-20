<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada Image Modifier.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */
class Library_image {
    
    /**
     * @var string  EN: Option for editng image the option is: resize | crop | resize_crop.
     *              ID: Opisi edit image pilihannya resize | crop | resize_crop.
     */
    public $edit_type = 'resize';
    
    /**
     *@var boolean  EN: Adjust width/heigh autmaticly for resizing proccess.
     *              ID: Jika yang di set adalah width maka height-nya menyesuaikan dan sebaliknya.
     */
    public $auto_ratio = true;
    
    /**
     * @var integer EN: New image width.
     */
    public $resize_width = 0;
    
    /**
     * @var integer EN: New image height.
     */
    public $resize_height = 0;
    
    /**
     * @var integer EN: Crope width size.
     */
    public $crop_width = 0;
    
    /**
     * @var integer EN: Crope height size.
     */
    public $crop_height = 0;
    
    /**
     * @var string  EN: Source file name.
     */
    public $file_name = '';
    
    /**
     * @var string  EN: Source full path location.
     */
    private $file_path;
    
    /**
     * @var string  EN: Source file info.
     */
    private $file_info = array();
    
    /**
     * @var string  EN: Define new file name.
     */
    public $new_file_name = '';
    
    /**
     * @var boolean EN: Remove any space in file name.
     *              ID: Hapus spasi pada nama file.
     */
    public $strip_spaces = true;
    
    /**
     * @var string  EN: Source folder location.
     */
    public $folder = '';
    
    /**
     * @var string  EN: Source image type: gif, jpg or png.
     */
    private $image_type;
    
    /**
     * @var integer EN: The value jpg compression. the range is 0 - 100.
     *              ID: Nilai untuk menentukan kwalitas kompresi jpg. Rentannya 0 - 100.
     */
    public $jpeg_compression = 90;
    
    /**
     * @var string  EN: Folder to placed new edited image.
     *              ID: Folder tempat menyimpan file baru.
     */
    public $save_to = '';
    
    /**
     * @var array   EN: Initiate the error mesages.
     *              ID: Set pesan-pesan error.
     */
    public $error_messages = array();
    
    
    /**
     * EN: Class constructor
     */
    public function __construct(){
        
        if( ! function_exists('imagecopyresampled') )
            Library_error::costume(500, 'Image resizing function that required by Library_images Class is not available.');
    }
    
    private function pre_error_checker(){
        
        /**
         * EN: Check is folder destionation has set.
         * ID: Pastikan folder tujan telah ditentukan.
         */
        if( empty($this->folder) ) {
            $this->error_messages[] = 'No folder located. Please define the folder location.';
            return false;
        }
        
        /**
         * EN: Does it folder exist?
         * ID: Apakah folder tersebut ada?
         */
        if( ! is_dir($this->folder) ) {
            $this->error_messages[] = 'The folder '.$this->folder.' doesn\'t exists please create it first.';
            return false;
        }
        
        /**
         * EN: Does it folder writable?
         * ID: Apakah folder tersebut bisa untuk tulis/hapus?
         */
        if( ! is_writable($this->folder) ) {
            $this->error_messages[] = 'The folder '.$this->folder.' is not writable.';
            return false;
        }
        
        /**
         * EN: Does the file exist?
         * ID: Apakah file yang akan dimodifiksi ada?
         */
        if( ! file_exists($this->file_path) ) {
            $this->error_messages[] = 'File '.$this->file_path.' doesn\'t exists.';
            return false;
        }
        
        return true;
    }
    
    private function error_handler(){
        
        if( ! $this->file_info || ! $this->file_info[0] || ! $this->file_info[1] ) {
            $this->error_messages[] = 'Unable to get image dimensions.';
            return false;
        }
        
        if ( ! function_exists( 'imagegif' ) && $this->image_type == IMAGETYPE_GIF || ! function_exists( 'imagejpeg' ) && $this->image_type == IMAGETYPE_JPEG || ! function_exists( 'imagepng' ) && $this->image_type == IMAGETYPE_PNG ) {
	    $this->error_messages[] = 'Filetype not supported.';
            return false;
	}
        
        return true;
    }
    
    public function edit($file_name){
        
	$this->file_name    = $file_name;
        $this->file_path    = $this->folder . '/' . $file_name;
        
        // EN: Pre condition cheking.
        if( ! $this->pre_error_checker() )
            return false;
        
        chmod($this->file_path, 0666);
	
        $this->file_info    = @getimagesize($this->file_path);
        $this->image_type   = $this->file_info[2];
        
        if ( ! $this->error_handler() )
            return false;
	
        $image = $this->create_image_from();
        
        if( ! empty($this->error_messages) )
            return false;
        
        if ( function_exists( 'imageantialias' ))
                imageantialias( $image, true );
        
        // EN: Initial heigh and widht variable.
        $image_width        = $this->file_info[0];
        $image_height       = $this->file_info[1];
        
        $image_new_width    = $this->file_info[0];
        $image_new_height   = $this->file_info[1];
        
        if( $this->resize_width > 0 && $this->resize_height == 0 ) {
            
            $image_new_width    = $this->resize_width;
            $image_ratio        = $image_width / $image_new_width;
            
            if($this->auto_ratio)
                $image_new_height = $image_height / $image_ratio;
        }
        
        if( $this->resize_height > 0 && $this->resize_width == 0 ) {
            
            $image_new_height   = $this->resize_height;
            $image_ratio        = $image_height / $image_new_height;
            
            if($this->auto_ratio)
                $image_new_width = $image_width / $image_ratio;
        }
        
        if( $this->resize_height > 0 && $this->resize_width > 0 && $this->edit_type == 'resize' ) {
            
            $image_new_height   = $this->resize_height;
            $image_new_height   = $this->resize_height;
        }
        
        //EN: Resizing
        if($this->edit_type == 'resize' || $this->edit_type == 'resize_crop') {
            
            $image_edited = imagecreatetruecolor( $image_new_width, $image_new_height);
            @imagecopyresampled( $image_edited, $image, 0, 0, 0, 0, $image_new_width, $image_new_height, $this->file_info[0], $this->file_info[1] );
        }
        
        //EN: Cropping process
        if($this->edit_type == 'crop' || $this->edit_type == 'resize_crop') {
            
            $cropped = imagecreatetruecolor($this->crop_width, $this->crop_height);
            imagecopyresampled($cropped, $image_edited, 0, 0, ( ($image_new_width/2) - ($this->crop_width/2) ), ( ($image_new_height/2) - ($this->crop_height/2) ), $this->crop_width, $this->crop_height, $this->crop_width, $this->crop_height);
            $image_edited = $cropped;
        }
        
        $this->create_image($image_edited);
	
	if( ! empty($this->error_messages) )
            return false;
        
	return true;
    }
    
    private function create_image_from(){
        
        // create the initial copy from the original file
        switch($this->image_type) {
            
            case IMAGETYPE_GIF:
                return imagecreatefromgif( $this->file_path );
                exit;
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg( $this->file_path );
                exit;
            case IMAGETYPE_PNG:
                return imagecreatefrompng( $this->file_path );
                exit;
            default:
                $this->error_messages[] = 'Unrecognized image format.';
                return false;
        }
    }
    
    private function create_image($image_edited){
        
        $file_extension = Library_upload::get_file_extension($this->file_name);
        $save_to        = ( ! empty($this->save_to) ) ? $this->save_to : $this->folder;
        $new_filename   = ( ! empty($this->new_file_name) )? $save_to . '/' . $this->new_file_name . '.' . $file_extension : $this->file_path;
        $new_filename   = ($this->strip_spaces) ? str_replace(' ', '_', $new_filename) : $new_filename;
        
        // move the new file
        if ( $this->image_type == IMAGETYPE_GIF ) {
            if ( ! imagegif( $image_edited, $new_filename ) )
                $this->error_messages[] = 'File path invalid.';
        }
        elseif ( $this->image_type == IMAGETYPE_JPEG ) {
            if (! imagejpeg( $image_edited, $new_filename, $this->jpeg_compression ) )
                $this->error_messages[] = 'File path invalid.';
        }
        elseif ( $this->image_type == IMAGETYPE_PNG ) {
            if (! imagepng( $image_edited, $new_filename ) )
                $this->error_messages[] = 'File path invalid.';
        }
    }
} // End Image Modifier Class