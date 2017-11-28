<?php

namespace KikCMS\Models;

use KikCmsCore\Classes\Model;

class TranslationValue extends Model
{
    const TABLE = 'cms_translation_value';

    const FIELD_KEY_ID        = 'key_id';
    const FIELD_LANGUAGE_CODE = 'language_code';
    const FIELD_VALUE         = 'value';
}