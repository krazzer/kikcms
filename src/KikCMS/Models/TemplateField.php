<?php

namespace KikCMS\Models;

use KikCMS\Classes\Model\Model;

class TemplateField extends Model
{
    const TABLE = 'cms_template_field';
    const ALIAS = 'tf';

    const FIELD_ID          = 'id';
    const FIELD_TEMPLATE_ID = 'template_id';
}