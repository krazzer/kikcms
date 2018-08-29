<?php


namespace KikCMS\Services\Util;

/**
 * Utility for handling strings
 */
class StringService
{
    /**
     * @param string $string
     * @param int $maxLength
     * @return string
     */
    public function truncate(string $string, int $maxLength = 50): string
    {
        return strlen($string) < $maxLength ? $string : mb_substr($string, 0, $maxLength) . '...';
    }
}