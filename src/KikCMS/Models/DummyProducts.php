<?php

namespace KikCMS\Models;

use KikCMS\Classes\Model\Model;

class DummyProducts extends Model
{
    const TABLE = 'products_dummy';
    const ALIAS = 'pr';

    const FIELD_PARENT_ID = 'parent_id';
}