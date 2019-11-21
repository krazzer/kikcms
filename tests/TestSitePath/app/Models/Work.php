<?php declare(strict_types=1);

namespace Website\Models;

use KikCmsCore\Classes\Model;

class Work extends Model
{
    const TABLE = 'test_work';
    const ALIAS = 'w';
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
