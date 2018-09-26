<?php

namespace KikCMS\Util;


class StringUtil
{
    /**
     * @param $string
     * @param bool $capitalizeFirstCharacter
     * @return mixed|string
     */
    public static function dashesToCamelCase(string $string, bool $capitalizeFirstCharacter = false): string
    {
        return self::toCamelCase($string, '-', $capitalizeFirstCharacter);
    }
    /**
     * @param $string
     * @param bool $capitalizeFirstCharacter
     * @return mixed|string
     */
    public static function underscoresToCamelCase(string $string, bool $capitalizeFirstCharacter = false): string
    {
        return self::toCamelCase($string, '_', $capitalizeFirstCharacter);
    }

    /**
     * @param string $string
     * @param string $charToConvert
     * @param bool $capitalizeFirstCharacter
     * @return string
     */
    private static function toCamelCase(string $string, string $charToConvert, bool $capitalizeFirstCharacter = false): string
    {
        $str = str_replace($charToConvert, '', ucwords($string, $charToConvert));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }
}