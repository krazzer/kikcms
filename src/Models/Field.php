<?php

namespace KikCMS\Models;

use KikCMS\Classes\Model\Model;

class Field extends Model
{
    const TABLE = 'cms_field';
    const ALIAS = 'f';

    const FIELD_ID       = 'id';
    const FIELD_NAME     = 'name';
    const FIELD_VARIABLE = 'variable';
}