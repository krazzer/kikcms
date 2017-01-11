<?php

namespace KikCMS\Models;

use KikCMS\Classes\Model\Model;

class Type extends Model
{
    const FIELD_ID = 'id';

    public function initialize()
    {
        $this->setSource('type');
    }
}