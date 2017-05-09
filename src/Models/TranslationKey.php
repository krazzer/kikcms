<?php

namespace KikCMS\Models;

use KikCMS\Classes\Model\Model;

class TranslationKey extends Model
{
    const TABLE = 'cms_translation_key';
    const ALIAS = 'tk';

    const FIELD_ID  = 'id';
    const FIELD_KEY = 'key';
}