<?php declare(strict_types=1);

namespace KikCMS\Util;


class ByteUtil
{
    /**
     * Converts php's human readable byte format to actual bytes, e.g. 32K becomes 32768 (32 * 1024)
     *
     * @param string $val
     * @return int
     */
    public static function stringToBytes(string $val): int
    {
        $sizes = ['b', 'k', 'm', 'g', 't', 'p', 'e', 'z', 'y'];
        $val   = trim($val);
        $last  = strtolower($val[strlen($val) - 1]);

        if ( ! in_array($last, $sizes)) {
            return (int) $val;
        }

        $val = (float) str_replace($last, '', $val);

        if ($index = array_search($last, $sizes)) {
            $val *= pow(1024, $index);
        }

        return (int) $val;
    }

    /**
     * Converts bytes to human readable string
     *
     * @param int $bytes
     * @param int $decimals
     *
     * @return string
     */
    public static function bytesToString(int $bytes, int $decimals = 0): string
    {
        $size   = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = (int) floor((strlen((string)$bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
}