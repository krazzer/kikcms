<?php

namespace KikCMS\Config;


class DbConfig
{
    const ERROR_CODE_FK_CONSTRAINT_FAIL = 1451;

    const SQL_DATE_FORMAT     = 'Y-m-d';
    const SQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

    const SQL_SORT_ASCENDING  = 'asc';
    const SQL_SORT_DESCENDING = 'desc';

    const SQL_SORT_DIRECTIONS = [
        self::SQL_SORT_ASCENDING,
        self::SQL_SORT_DESCENDING,
    ];
}