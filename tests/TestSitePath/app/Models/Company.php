<?php


namespace Website\Models;


use KikCmsCore\Classes\Model;

class Company extends Model
{
    const TABLE = 'test_company';
    const ALIAS = 'c';

    const FIELD_ID   = 'id';
    const FIELD_NAME = 'name';

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->hasOne(self::FIELD_ID, Person::class, Person::FIELD_COMPANY_ID, ['alias' => 'person']);
    }
}