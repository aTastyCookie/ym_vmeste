<?php

namespace Vmeste\SaasBundle\Util;


class Clear
{
    /**
     * @param  mixed $input
     * @param mixed $default
     * @return string
     */
    public static function integer($input, $default = false)
    {
        $result = (int) self::string_without_quotes($input);
        if($result === 0 && $default !== false) $result = $default;
        return $result;
    }

    /**
     * @param  mixed $input
     * @param mixed $default
     * @return string
     */
    public static function number($input, $default = false)
    {
        $result = (float) self::string_without_quotes($input);
        if($result === 0 && $default !== false) $result = $default;
        return $result;
    }

    /**
     * @param  string $input
     * @param  boolean $htmlchars
     * @return string
     */
    public static function string_without_quotes($input, $htmlchars = true)
    {
        return str_replace("'", '', str_replace('"', '', self::removeCRLF($input, $htmlchars)));
    }

    /**
     * @param  string $input
     * @param  boolean $htmlchars
     * @return string
     */
    public static function removeCRLF($input, $htmlchars = true, $quotes = true) {
        $newString = '';
        if($htmlchars)
            if($quotes)
                $input = htmlspecialchars(strip_tags($input), ENT_QUOTES);
            else $input = htmlspecialchars(strip_tags($input), ENT_NOQUOTES);
        else
            $input = strip_tags($input);
        for ($i = 0; $i < strlen($input); $i++) {
            if (ord($input{$i}) != 10 && ord($input{$i}) != 13) {
                $newString .= $input{$i};
            } else {
                break;
            }
        }
        return $newString;
    }
} 