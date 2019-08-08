<?php

namespace Website\Models;

use KikCmsCore\Classes\Model;

class DataTableTestChild extends Model
{
    const TABLE = 'test_datatable_test_child';
    const ALIAS = 'dtc';
    const FIELD_ID = 'id';
    const FIELD_NAME = 'name';
    const FIELD_PARENT_ID = 'parent_id';

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();
    }
}
