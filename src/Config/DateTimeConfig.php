<?php


namespace KikCMS\Config;


class DateTimeConfig
{
    const PHP_TO_MOMENT_REPLACEMENTS = [
        'd' => 'DD',
        'D' => 'ddd',
        'j' => 'D',
        'l' => 'dddd',
        'N' => 'E',
        'S' => 'o',
        'w' => 'e',
        'z' => 'DDD',
        'W' => 'W',
        'F' => 'MMMM',
        'm' => 'MM',
        'M' => 'MMM',
        'n' => 'M',
        'o' => 'YYYY',
        'Y' => 'YYYY',
        'y' => 'YY',
        'a' => 'a',
        'A' => 'A',
        'g' => 'h',
        'G' => 'H',
        'h' => 'hh',
        'H' => 'HH',
        'i' => 'mm',
        's' => 'ss',
        'u' => 'SSS',
        'U' => 'X',
    ];

    /**
     * Convert php date format to momentjs format
     *
     * @param string $format
     * @return string
     */
    public static function phpToMoment(string $format): string
    {
        return strtr($format, self::PHP_TO_MOMENT_REPLACEMENTS);
    }
}