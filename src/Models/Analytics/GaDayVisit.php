<?php

namespace KikCMS\Models\Analytics;


use KikCMS\Classes\Model\Model;

class GaDayVisit extends Model
{
    const TABLE = 'ga_day_visit';
    const ALIAS = 'gdv';

    const FIELD_DATE          = 'date';
    const FIELD_VISITS        = 'visits';
    const FIELD_UNIQUE_VISITS = 'unique_visits';
}