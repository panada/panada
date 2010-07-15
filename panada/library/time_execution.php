<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * EN: Panada Time Execution tools.
 * ID: Tools untuk menghitung waktu eksekusi script.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman.
 * @since	Version 0.1
 */
class Library_time_execution {
    
    /**
     * EN: Calculate the microtime.
     * ID: Kalkulasi waktu.
     *
     * @return int
     */
    static function _microtime(){
        
        $microtime = explode(' ', microtime());
        return $microtime[1] + $microtime[0];
    }
    
    /**
     * EN: Start the count execution.
     * ID: Mulai menghitung.
     *
     * @return boolean
     */
    static function start() {
        
        $GLOBALS['timestart'] = self::_microtime();
        return true;
    }
    
    /**
     * EN: Stop the count.
     * ID: Akhir menghitung.
     *
     * @param string
     */
    static function stop() {
        
        $timetotal = self::_microtime() - $GLOBALS['timestart'];
        $r = number_format($timetotal, 4);
        
        echo $r;
    }
    
} //End Library_time_execution class