<?php


namespace Models;


use KikCmsCore\Classes\Model;
use Phalcon\Mvc\Model\Resultset\Simple;

/**
 * @property Company|Simple $company
 */
class Person extends Model
{
    const TABLE = 'test_person';
    const ALIAS = 'p';

    const FIELD_ID         = 'id';
    const FIELD_NAME       = 'name';
    const FIELD_COMPANY_ID = 'company_id';

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->belongsTo(self::FIELD_COMPANY_ID, Company::class, Company::FIELD_ID, ['alias' => 'company']);
        $this->hasMany(self::FIELD_ID, PersonInterest::class, PersonInterest::FIELD_PERSON_ID, ['alias' => 'personInterests']);
    }
}