<?php

namespace Resources;

/**
 * Panada Encyption class.
 *
 * @link	http://panadaframework.com/, http://heiswayi.github.io/php-encryption-decryption-and-password-hashing.html
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
    public function encrypt($data)
    {
        $salt = substr(md5(mt_rand(), true), 8);

        $key = md5($this->key . $salt, true);
        $iv  = md5($key . $this->key . $salt, true);

        $ct = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);

        return base64_encode('Salted__' . $salt . $ct);
    }

    /**
     * Decryption method
     *
     * @var string
     * @return string
     */
    public function decrypt($data)
    {
        $data = base64_decode($data);
        $salt = substr($data, 8, 8);
        $ct   = substr($data, 16);

        $key = md5($this->key . $salt, true);
        $iv  = md5($key . $this->key . $salt, true);

        $pt = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ct, MCRYPT_MODE_CBC, $iv);

        return $pt;
    }
}
