<?php

namespace Website\Models;

use KikCmsCore\Classes\Model;

class SimpleObject extends Model
{
    const TABLE = 'test_simple_object';
    const ALIAS = 'so';

    const FIELD_ID   = 'id';
    const FIELD_NAME = 'name';

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();
    }
}
