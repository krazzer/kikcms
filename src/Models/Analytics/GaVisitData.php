<?php

namespace KikCMS\Models\Analytics;


use KikCmsCore\Classes\Model;

class GaVisitData extends Model
{
    const TABLE = 'cms_analytics_metric';
    const ALIAS = 'am';

    const FIELD_DATE   = 'date';
    const FIELD_TYPE   = 'type';
    const FIELD_VALUE  = 'value';
    const FIELD_VISITS = 'visits';
}