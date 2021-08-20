<?php

namespace Website\Models;

use KikCMS\Classes\Model;

class CompanyType extends Model
{
    const TABLE = 'test_company_type';
    const ALIAS = 'ct';

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