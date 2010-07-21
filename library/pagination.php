<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada Pagination class.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman. Thanks to Wordpress {@link http://codex.wordpress.org/Function_Reference/paginate_links}
 * @since	Version 0.1
 */

class Library_pagination {
    
    /**
     * @var boolean EN: Show the number. If you set it to false, so it would return next and previous only.
     *              ID: Tampilkan angka pada paging. Jika diset false maka hanya akan menampilkan next dan previous.
     */
    public $show_number = true;
    
    /**
     * @var string  EN: http://example.com/allposts/%_% : %_% is replaced by format properties.
     *              ID: Format url website. Setiap string %_% akan digantikan dengan format paging.
     */
    public $url_reference = '';
    
    /**
     * @var string  EN: http://example.com/allposts/%#% : %#% is replaced by the page number.
     *              ID: Setiap karakter %#% akan digantikan dengan angka 'page'.
     */
    public $format = '/%#%/';
    
    /**
     * @var integer EN: Total number all record.
     *              ID: Jumlah total dari semua record.
     */
    public $total = 1;
    
    /**
     * @var integer EN: Limit record per page.
     *              ID: Limit record yang ditampilkan dalam satu halaman.
     */
    public $limit = 5;
    
    /**
     * @var integer EN: Current page location: welcome/comments/1, welcome/comments/2, welcome/comments/3 etc...
     *              ID: Nilai 'page' saat ini.
     */
    public $current = 0;
    
    /**
     * @var boolean EN: Show all page number and it link. I dont think you gonna like this.
     *              ID: Tampilkan semua angka dari paging. Jika record-nya banyak akan mengakibatkan penumpukan link paging.
     */
    public $show_all = false;
    
    /**
     * @var boolean EN: If you wanna get the output without any href html tag, then set $no_href = true;. You will get something like this Array([link] => paging link [value] => paging value) for each item.
     *              ID: Jika output yang diinginkan tidak dibungkus dengan href html, maka set parameter ini menjadi true. Outputnya akan menjadi Array([link] => paging link [value] => paging value) untuk setiap item.
     */
    public $no_href = false;
    
     /**
      * @var boolean EN: Show previous and next link.
      *             ID: Tampilkan previous dan next.
      */
    public $prev_next = true;
    
    /**
     * @var string EN: The "previous" html character/string.
     *              ID: String untuk previous misalnya "Sebelumnya".
     */
    public $prev_text = '&laquo; Previous';
    
    /**
     * @var string EN: The "next" html character/string.
     *              ID: String untuk next misalnya "Berikutnya".
     */
    public $next_text = 'Next &raquo;';
    
    /**
     * @var string EN: The separator between one block number to enother.
     *              ID: Pemisah antara blok angka yang satu dengan yang lainnya. contoh: 1 2 3 ... 22 23 24 ... 100 101 102
     */
    public $group_separator = '...';
    
    /**
     * @var integer EN: How many numbers on either end including the end.
     *              ID: Berapa banyak jumlah angka yang ditampilkan di blok angka terakhir. Jika di set 2 maka hasilnya akan 1 2 ... 10 11 12 ... 100 101
     */
    public $end_size = 1;
    
    /**
     * @var integer EN: How many numbers to either side of current not including current.
     *              ID: Jumlah angka sebelum dan sesudah angka paging saat ini. Jika di set 2 maka hasilnya akan 1 2 ... 9 10 [11] 12 13 ... 100 101
     */
    public $mid_size = 2;
    
    
    /**
     * EN: Class contructor
     */
    public function __construct(){
        
        /**
         * EN: Make sure the argument type only integer.
         * ID: Memastikan tipe datanya integer.
         */
        $this->total    = (int) $this->total;
        $this->limit    = (int) $this->limit;
        $this->current  = (int) $this->current;
        $this->end_size = (int) $this->end_size;
        $this->mid_size = (int) $this->mid_size;
    }
    
    /**
     * EN: Create pagination.
     * ID: Buat pagination.
     *
     * @return array
     */
    public function get_url(){
	
        $total    = ceil($this->total / $this->limit);
        if ( $total < 2 )
            return;
        
        $end_size   = (0 < $this->end_size) ? $this->end_size : 1; // Out of bounds?  Make it the default.
        $mid_size   = (0 <= $this->mid_size) ? $this->mid_size : 2;
        
        $r          = '';
        $paging_url = array();
        $n          = 0;
        $dots       = false;
        
        if ( $this->prev_next && $this->current && 1 < $this->current ) {
           
            $link         = str_replace('%#%', $this->current - 1, $this->base);
            $paging_url[] = ( $this->no_href )? array('link' => $link, 'value' => $this->prev_text) : '<a href="'.$link.'">'.$this->prev_text.'</a>';
            
        }
        
        for ( $n = 1; $n <= $total; $n++ ) {
            
            if ( $n == $this->current ) {
                
                if($this->show_number){
                    
                    $paging_url[] = ( $this->no_href )? array('link' => '', 'value' => $n) : '<span>'.$n.'</span>';
                    $dots = true;
                }
            }
            else {
                
                if ( $this->show_all || ( $n <= $end_size || ( $this->current && $n >= $this->current - $mid_size && $n <= $this->current + $mid_size ) || $n > $total - $end_size ) ) {
                    
                    $link = str_replace('%_%', ($n == 1)? '' : $this->format, $this->base);
                    $link = str_replace('%#%', $n, $link);
                    
                    if($this->show_number){
                        
                        $paging_url[] = ( $this->no_href )? array('link' => $link, 'value' => $n) : '<a href="'.$link.'">'.$n.'</a>';
                        $dots = true;
                    }
                }
                elseif ( $dots && !$this->show_all ) {
                    
                    $paging_url[] = ( $this->no_href )? array('link' => '', 'value' => $this->group_separator) : '<span>'.$this->group_separator.'</span>';
                    $dots = false;
                }
            }
            
        }
        
        if ( $this->prev_next && $this->current && ( $this->current < $total || -1 == $total ) ) {
            
            $link           = str_replace('%_%', $this->format, $this->base);
            $link           = str_replace('%#%', $this->current + 1, $link);
            
            $paging_url[]   = ( $this->no_href )? array('link' => $link, 'value' => $this->next_text) : '<a href="'.$link.'">'.$this->next_text.'</a>';
        }
        
        return $paging_url;
    }
    
} // End Pagination Class.