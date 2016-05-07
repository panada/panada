<?php

namespace Resources;

/**
 * Panada Encyption class.
 *
 * @link	http://panadaframework.com/
 *
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @since	Version 0.1
 */
class Encryption
{
    private $key = '__&&^^%%%#$@';

    public function __construct($key = false)
    {
        if($key){
            $this->key = $key;
        }
    }

    /**
     * Produce encryption
     *
     * @var string
     * @return string
     */
    public function encrypt($string)
    {
        $return = '';

        for ($i = 0; $i < strlen($string); $i++) {
            $str     = substr($string, $i, 1);
            $return .= chr(ord($str) + ord(substr($this->key, ($i % strlen($this->key)) - 1, 1)));
        }

        return base64_encode($return);
    }

    /**
     * Decryption method
     *
     * @var string
     * @return string
     */
    public function decrypt($string)
    {
        $string = base64_decode($string);

        $return = '';

        for ($i = 0; $i < strlen($string); $i++) {
            $str     = substr($string, $i, 1);
            $return .= chr(ord($str) - ord(substr($this->key, ($i % strlen($this->key)) - 1, 1)));
        }

        return $return;
    }
}
