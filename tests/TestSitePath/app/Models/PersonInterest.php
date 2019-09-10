<?php


namespace Website\Models;


use KikCmsCore\Classes\Model;

/**
 * @property TestPerson $person
 */
class PersonInterest extends Model
{
    const TABLE = 'test_person_interest';
    const ALIAS = 'pi';

    const FIELD_ID          = 'id';
    const FIELD_PERSON_ID   = 'person_id';
    const FIELD_INTEREST_ID = 'interest_id';

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->belongsTo(self::FIELD_PERSON_ID, TestPerson::class, TestPerson::FIELD_ID, ['alias' => 'person']);
    }
}