<?php

namespace KikCMS\Models;

use KikCMS\Classes\Model\Model;

class ProductType extends Model
{
    const FIELD_TYPE_ID    = 'type_id';
    const FIELD_PRODUCT_ID = 'product_id';

    public function initialize()
    {
        $this->setSource('product_type');
    }
}