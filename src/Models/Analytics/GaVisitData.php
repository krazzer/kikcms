<?php

namespace KikCMS\Models\Analytics;


use KikCMS\Classes\Model\Model;

class GaVisitData extends Model
{
    const TABLE = 'ga_visit_data';
    const ALIAS = 'gvd';

    const FIELD_DATE   = 'date';
    const FIELD_TYPE   = 'type';
    const FIELD_VALUE  = 'value';
    const FIELD_VISITS = 'visits';
}