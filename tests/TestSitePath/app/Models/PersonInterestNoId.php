<?php declare(strict_types=1);

namespace Website\Models;

use KikCmsCore\Classes\Model;

class PersonInterestNoId extends Model
{
    const TABLE = 'test_person_interest_no_id';
    const ALIAS = 'pini';
    const FIELD_PERSON_ID = 'person_id';
    const FIELD_INTEREST_ID = 'interest_id';

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();
    }
}
