<?php


namespace Models;


use KikCmsCore\Classes\Model;

/**
 * @property Person $person
 */
class Interest extends Model
{
    const TABLE = 'test_interest';
    const ALIAS = 'i';

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