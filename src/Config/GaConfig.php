<?php

namespace KikCMS\Config;

class GaConfig
{
    const GA4_LAUNCH_DATE   = '2020-10-01';

    const DIMENSION_SOURCE         = 'sessionSource';
    const DIMENSION_OS             = 'operatingSystem';
    const DIMENSION_PATH           = 'pagePath';
    const DIMENSION_BROWSER        = 'browser';
    const DIMENSION_COUNTRY        = 'country';
    const DIMENSION_RESOLUTION     = 'screenResolution';
    const DIMENSION_DEVICECATEGORY = 'deviceCategory';

    const METRIC_SOURCE     = 'source';
    const METRIC_OS         = 'os';
    const METRIC_PATH       = 'page';
    const METRIC_BROWSER    = 'browser';
    const METRIC_COUNTRY    = 'location';
    const METRIC_RESOLUTION = 'resolution';
}