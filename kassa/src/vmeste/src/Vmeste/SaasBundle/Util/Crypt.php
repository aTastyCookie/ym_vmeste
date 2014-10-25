<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 10/13/14
 * Time: 9:50 PM
 */

namespace Vmeste\SaasBundle\Util;


class Crypt
{

    static function encrypt($key, $value)
    {
        $binaryKey = pack("H*", $key);

        $ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);

        $cipherText = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $binaryKey, $value, MCRYPT_MODE_CBC, $iv);
        $encodedValue = $iv . $cipherText;

        return base64_encode($encodedValue);
    }

    static function decrypt($key, $value)
    {

        $binaryKey = pack("H*", $key);
        $cipherTextDec = base64_decode($value);

        $ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $ivDec = substr($cipherTextDec, 0, $ivSize);

        $cipherTextDec = substr($cipherTextDec, $ivSize);
        $decodedValue = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $binaryKey, $cipherTextDec, MCRYPT_MODE_CBC, $ivDec);

        return $decodedValue;
    }

} 