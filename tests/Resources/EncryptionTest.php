<?php

namespace Tests\Resources;

class EncryptionTest extends \PHPUnit_Framework_TestCase
{
    public function testEncrypt()
    {
        $encryption = new \Resources\Encryption;

        $string = 'panada';

        $encrypted = $encryption->encrypt($string);
        $decrypted = $encryption->decrypt($encrypted);

        $this->assertSame($string, $decrypted);
    }
}
