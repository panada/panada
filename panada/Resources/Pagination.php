<?php
/**
 * Panada Pagination class.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman. Thanks to Wordpress {@link http://codex.wordpress.org/Function_Reference/paginate_links}
 * @since	Version 0.1
 */
namespace Resources;

class Pagination {
    
    /**
     * @var boolean Show the number. If you set it to false, so it would return next and previous only.
     */
    public $show_number = true;
    
    /**
     * @var string  http://example.com/allposts/%_% : %_% is replaced by format properties.
     */
    public $url_reference = '';
    
    /**
     * @var string  http://example.com/allposts/%#% : %#% is replaced by the page number.
     */
    public $format = '/%#%/';
    
    /**
     * @var integer Total number all record.
     */
    public $total = 1;
    
    /**
     * @var integer Limit record per page.
     */
    public $limit = 5;
    
    /**
     * @var integer Current page location: welcome/comments/1, welcome/comments/2, welcome/comments/3 etc...
     */
    public $current = 0;
    
    /**
     * @var boolean Show all page number and it link. I dont think you gonna like this.
     */
    public $show_all = false;
    
    /**
     * @var boolean If you wanna get the output without any href html tag, then set $no_href = true;. You will get something like this Array([link] => paging link [value] => paging value) for each item.
     */
    public $no_href = false;
    
     /**
      * @var boolean Show previous and next link.
      */
    public $prev_next = true;
    
    /**
     * @var string The "previous" html character/string.
     */
    public $prev_text = '&laquo; Previous';
    
    /**
     * @var string The "next" html character/string.
     */
    public $next_text = 'Next &raquo;';
    
    /**
     * @var string The separator between one block number to enother.
     */
    public $group_separator = '...';
    
    /**
     * @var integer How many numbers on either end including the end.
     */
    public $end_size = 1;
    
    /**
     * @var integer How many numbers to either side of current not including current.
     */
    public $mid_size = 2;
    
    
    /**
     * Class contructor
     */
    public function __construct(){
        
        /**
         * Make sure the argument type only integer.
         */
        $this->total    = (int) $this->total;
        $this->limit    = (int) $this->limit;
        $this->current  = (int) $this->current;
        $this->end_size = (int) $this->end_size;
        $this->mid_size = (int) $this->mid_size;
    }
    
    /**
     * Create pagination.
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
    
}