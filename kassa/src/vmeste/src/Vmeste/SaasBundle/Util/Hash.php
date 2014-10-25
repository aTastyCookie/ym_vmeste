<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/16/14
 * Time: 11:11 PM
 */

namespace Vmeste\SaasBundle\Util;


class Hash
{
    /**
     * @return string
     */
    public static function generateRecoverToken()
    {
        return sha1(time() . rand() . md5(time()));
    }
} 