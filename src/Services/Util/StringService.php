<?php declare(strict_types=1);


namespace KikCMS\Services\Util;

/**
 * Utility for handling strings
 */
class StringService
{
    /**
     * @param $string
     * @param bool $capitalizeFirstCharacter
     * @return mixed|string
     */
    public function dashesToCamelCase(string $string, bool $capitalizeFirstCharacter = false): string
    {
        return $this->toCamelCase($string, '-', $capitalizeFirstCharacter);
    }

    /**
     * @param string $string
     * @param int $maxLength
     * @return string
     */
    public function truncate(string $string, int $maxLength = 50): string
    {
        return strlen($string) < $maxLength ? $string : mb_substr($string, 0, $maxLength) . '...';
    }

    /**
     * @param $string
     * @param bool $capitalizeFirstCharacter
     * @return mixed|string
     */
    public function underscoresToCamelCase(string $string, bool $capitalizeFirstCharacter = false): string
    {
        return $this->toCamelCase($string, '_', $capitalizeFirstCharacter);
    }

    /**
     * @param string $string
     * @param string $charToConvert
     * @param bool $capitalizeFirstCharacter
     * @return string
     */
    private function toCamelCase(string $string, string $charToConvert, bool $capitalizeFirstCharacter = false): string
    {
        $str = str_replace($charToConvert, '', ucwords($string, $charToConvert));

        if ( ! $capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }
}