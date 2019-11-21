<?php declare(strict_types=1);

namespace Website\Models;

use KikCmsCore\Classes\Model;

class Interest extends Model
{
    const TABLE = 'test_interest';
    const ALIAS = 'i';
    const FIELD_ID = 'id';
    const FIELD_NAME = 'name';

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();
    }
}
