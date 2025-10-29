<?php declare(strict_types=1);


namespace KikCMS\Services\Util;

/**
 * Utility for handling strings
 */
class StringService
{
    /**
     * @param int $length
     * @return string
     */
    public function createRandomString(int $length = 32): string
    {
        $decimal = (float) hexdec(bin2hex(random_bytes($length)));

        return substr($this->floatToBaseString($decimal), 0, $length);
    }

    /**
     * @param string $string
     * @param bool $capitalizeFirstCharacter
     * @return string
     */
    public function dashesToCamelCase(string $string, bool $capitalizeFirstCharacter = false): string
    {
        return $this->toCamelCase($string, '-', $capitalizeFirstCharacter);
    }

    /**
     * @param string $string
     * @return string
     */
    public function camelToDashCase(string $string): string
    {
        $output = preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $string);
        $output = preg_replace('/([A-Z])([A-Z][a-z])/', '$1-$2', $output);

        return strtolower($output);
    }

    /**
     * @param float $number
     * @param string $chars
     * @return string
     */
    public function floatToBaseString(float $number, string $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
    {
        if ( ! $number) {
            return '0';
        }

        $base  = strlen($chars);
        $index = '';
        $out   = '';

        if ( ! $base) {
            $base = strlen($index);
        } else {
            $index = substr($chars, 0, $base);
        }

        for ($t = floor(log10($number) / log10($base)); $t >= 0; $t--) {
            $a      = (int) floor($number / pow($base, $t));
            $out    = $out . substr($index, $a, 1);
            $number = $number - ($a * pow($base, $t));
        }

        return $out;
    }

    /**
     * @param string $string
     * @param int $maxLength
     * @return string
     */
    public function truncate(string $string, int $maxLength = 50): string
    {
        $string = html_entity_decode($string);

        return mb_strlen($string) < $maxLength ? $string : mb_substr($string, 0, $maxLength) . '...';
    }

    /**
     * @param string $string
     * @param bool $capitalizeFirstCharacter
     * @return string
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