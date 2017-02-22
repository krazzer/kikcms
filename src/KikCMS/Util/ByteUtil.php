<?php

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
        $val  = trim($val);
        $last = strtolower($val[strlen($val) - 1]);

        switch ($last) {
            case 'g':
                $val *= (1024 * 1024 * 1024);
            break;
            case 'm':
                $val *= (1024 * 1024);
            break;
            case 'k':
                $val *= 1024;
            break;
        }

        return $val;
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
        $size   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = (int) floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
}