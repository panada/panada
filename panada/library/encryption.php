<?php defined('THISPATH') or die('Tidak diperkenankan mengakses file secara langsung.');
/**
 * Panada Encyption class.
 *
 * @package	Panada
 * @subpackage	Library
 * @author	Iskandar Soesman
 * @since	Version 0.1
 */

class Library_encryption {
    
    /**
     * @var string  EN: Encoding type. none | base_64 | hexa_decimal
     *              ID: Tipe encoding. Pilihannya adalah: none | base_64 | hexa_decimal
     */
    public $encode_type = 'base_64';
    
    /**
     * @var string  EN: The encrypt/decrypt key.
     *              ID: Key untuk encrypt/decrypt.
     */
    public $key;
    
    /**
     * EN: Produce encryption without Mcrypt modul.
     * ID: Buat enkripsi tanpa modul Mcrypt.
     *
     * @var string
     * @var string
    */
    function encrypt($string, $key = ''){
        
        $this->key = $key;
        return $this->simple_encrypt($string);
    }
    
    /**
     * EN: Decryption method without Mcrypt modul.
     * ID: Dekripsi tanpa modul Mcrypt.
     *
     * @var string
     * @var string
     * @return string
     * @access public
    */
    function decrypt($string, $key = ''){
        
        $this->key = $key;
        return $this->simple_decrypt($string);
    }
    
    /**
     * EN: Create the ciphertext string.
     * ID: Ubah string menjadi string acak.
     *
     * @param string
     * @return string
     * @access public
     */
    private function simple_encrypt($string){
        
        $return = '';
        
        for($i=0; $i < strlen($string); $i++) {
            
            $str     = substr($string, $i, 1);
            $return .= chr( ord($str) + ord( substr($this->key, ($i % strlen($this->key))-1, 1) ) );
        }
      
        return $this->encode($return);
    }
    
    /**
     * EN: Create the plain text string.
     * ID: Ubah string acak menjadi string terbaca.
     *
     * @param string
     * @return string
     * @access public
     */
    private function simple_decrypt($string){
        
        $return = '';
        
        $string = $this->decode($string);
      
        for($i=0; $i<strlen($string); $i++) {
            
            $str     = substr($string, $i, 1);
            $return .= chr( ord($str) - ord( substr($this->key, ($i % strlen($this->key))-1, 1) ) );
        }
        
        return $return;
    }
    
    /**
     * EN: Encode the encypted string.
     * ID: Enocode string yang telah terenkripsi.
     *
     * @param string
     * @return string
     * @access private
     */
    private function encode($string){
        
        if($this->encode_type == 'base_64')
            return base64_encode($string);
        elseif($this->encode_type == 'hexa_decimal')
            return $this->hexa_encode($string);
        else
            return $string;
    }
    
    /**
     * EN: Decode the encypted string.
     * ID: Decode string yang telah terenkripsi.
     *
     * @param string
     * @return string
     * @access private
     */
    private function decode($string){
        
        if($this->encode_type == 'base_64')
            return base64_decode($string);
        elseif($this->encode_type == 'hexa_decimal')
            return $this->hexa_decode($string);
        else
            return $string;
    }
    
    /**
     * EN: Encode the binary into hexadecimal.
     * ID: Encode binary menjadi hexadecimal.
     *
     * @param string
     * @return string
     * @access private
     */
    private function hexa_encode($string){
        
        $string = (string) $string;
        return preg_replace("'(.)'e", "dechex(ord('\\1'))", $string);
    }
    
    /**
     * EN: Decode the hexadecimal code into binary.
     * ID: Decode binary menjadi hexadecimal.
     *
     * @param string
     * @return string
     * @access private
     */
    private function hexa_decode($string){
        
        $string = (string) $string;
        return preg_replace("'([\S,\d]{2})'e", "chr(hexdec('\\1'))", $string);
    }
    
} // End Encryption class.