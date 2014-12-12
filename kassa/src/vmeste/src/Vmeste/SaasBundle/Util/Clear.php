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
    public static function string_without_quotes($input, $htmlchars = true, $quotes = true)
    {
        return str_replace("'", '', str_replace('"', '', self::removeCRLF($input, $htmlchars, $quotes)));
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
                $input = htmlentities(strip_tags($input), ENT_QUOTES, 'UTF-8', false);
            else $input = htmlentities(strip_tags($input), ENT_NOQUOTES, 'UTF-8', false);
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