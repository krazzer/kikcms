<?php


namespace Website\Models;


use KikCmsCore\Classes\Model;

class DataTableTest extends Model
{
    public const TABLE = 'test_datatable_test';
    public const ALIAS = 'tdt';

    const FIELD_ID              = 'id';
    const FIELD_TEXT            = 'text';
    const FIELD_FILE_ID         = 'file_id';
    const FIELD_CHECKBOX        = 'checkbox';
    const FIELD_DATE            = 'date';
    const FIELD_MULTICHECKBOX   = 'multicheckbox';
    const FIELD_DATATABLESELECT = 'datatableselect';
    const FIELD_TEXTAREA        = 'textarea';
    const FIELD_HIDDEN          = 'hidden';
    const FIELD_AUTOCOMPLETE    = 'autocomplete';
    const FIELD_PASSWORD        = 'password';
    const FIELD_WYSIWYG         = 'wysiwyg';

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        $this->hasMany(self::FIELD_ID, DataTableTestChild::class, DataTableTestChild::FIELD_PARENT_ID, ['alias' => 'dataTableTestChildren']);
    }
}