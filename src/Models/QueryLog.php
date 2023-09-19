<?php declare(strict_types=1);

namespace KikCMS\Models;

use KikCmsCore\Classes\Model;

class QueryLog extends Model
{
    const TABLE = 'cms_query_log';
    const ALIAS = 'ql';

    const FIELD_ID       = 'id';
    const FIELD_QUERY    = 'query';
    const FIELD_CALLED   = 'called';
    const FIELD_TIME     = 'time';
    const FIELD_DATETIME = 'datetime';
    const FIELD_HASH     = 'hash';
}
