<?php declare(strict_types=1);

namespace KikCMS\Classes\Database;


use DateTime;
use KikCmsCore\Config\DbConfig;

/**
 * Represents the current dateTime.
 * On insert it will convert to a format SQL uses
 */
class Now
{
    /** @var DateTime */
    private $dateTime;

    public function __construct()
    {
        $this->dateTime = new DateTime();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->dateTime->format(DbConfig::SQL_DATETIME_FORMAT);
    }

    /**
     * @return string
     */
    public function str(): string
    {
        return $this->__toString();
    }
}