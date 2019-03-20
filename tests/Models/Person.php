<?php


namespace Models;


use KikCmsCore\Classes\Model;

class Person extends Model
{
    const TABLE = 'test_person';
    const ALIAS = 'p';

    const FIELD_ID   = 'id';
    const FIELD_NAME = 'name';
}