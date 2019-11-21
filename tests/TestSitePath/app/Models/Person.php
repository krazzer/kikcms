<?php declare(strict_types=1);

namespace Website\Models;

use KikCmsCore\Classes\Model;

class Person extends Model
{
    const TABLE = 'test_person';
    const ALIAS = 'p';
    const FIELD_ID = 'id';
    const FIELD_NAME = 'name';
    const FIELD_COMPANY_ID = 'company_id';
    const FIELD_IMAGE_ID = 'image_id';
    const FIELD_DISPLAY_ORDER = 'display_order';
    const FIELD_CREATED = 'created';

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();
    }
}
