<?php

namespace KikCMS\Models;

use KikCMS\Classes\Model\Model;

class DummyProducts extends Model
{
    const TABLE = 'products_dummy';
    const ALIAS = 'pr';

    const FIELD_ID          = 'id';
    const FIELD_TITLE       = 'title';
    const FIELD_PRICE       = 'price';
    const FIELD_STOCK       = 'stock';
    const FIELD_SALE        = 'sale';
    const FIELD_CATEGORY_ID = 'category_id';
    const FIELD_DESCRIPTION = 'description';
    const FIELD_PARENT_ID   = 'parent_id';
}