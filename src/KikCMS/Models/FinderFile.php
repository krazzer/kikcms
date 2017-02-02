<?php

namespace KikCMS\Models;

use DateTime;
use KikCMS\Classes\Model\Model;

class FinderFile extends Model
{
    const TABLE = 'finder_file';

    const FIELD_ID = 'id';

    /**
     * @return DateTime
     */
    public function getCreated()
    {
        return new DateTime($this->created);
    }
}