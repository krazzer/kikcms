<?php declare(strict_types=1);

namespace KikCMS\Config;


class KikCMSConfig
{
    const ENV_DEV  = 'dev';
    const ENV_PROD = 'prod';

    const DATE_FORMAT = 'Y-m-d';

    const NAMESPACE_SEPARATOR = '\\';

    const NAMESPACE_KIKCMS     = 'KikCMS';
    const NAMESPACE_WEBSITE    = 'Website';
    const NAMESPACE_DATATABLES = 'DataTables';

    const NAMESPACE_PATH_MODELS      = '\\Website\\Models\\';
    const NAMESPACE_PATH_FORMS       = '\\Website\\Forms\\';
    const NAMESPACE_PATH_DATATABLES  = '\\Website\\DataTables\\';
    const NAMESPACE_PATH_OBJECTLIST  = '\\Website\\ObjectList\\';
    const NAMESPACE_PATH_OBJECTS     = '\\Website\\Objects\\';
    const NAMESPACE_PATH_SERVICES    = '\\Website\\Services\\';
    const NAMESPACE_PATH_TASKS       = '\\Website\\Tasks\\';
    const NAMESPACE_PATH_CONTROLLERS = '\\Website\\Controllers\\';
    const NAMESPACE_PATH_CLASSES     = '\\Website\\Classes\\';

    const NAMESPACE_PATH_CMS_CONTROLLERS = '\\KikCMS\\Controllers\\';
    const NAMESPACE_PATH_CMS_SERVICES    = '\\KikCMS\\Services\\';
    const NAMESPACE_PATH_CMS_TASKS       = '\\KikCMS\\Tasks\\';

    const NAMESPACE_PATH_PHALCON_VALIDATORS = 'Phalcon\\Validation\\Validator\\';

    const CONTENT_TYPES = [
        'text'         => self::CONTENT_TYPE_TEXT,
        'textarea'     => self::CONTENT_TYPE_TEXTAREA,
        'int'          => self::CONTENT_TYPE_INT,
        'checkbox'     => self::CONTENT_TYPE_CHECKBOX,
        'tinymce'      => self::CONTENT_TYPE_TINYMCE,
        'image'        => self::CONTENT_TYPE_IMAGE,
        'file'         => self::CONTENT_TYPE_FILE,
        'tab'          => self::CONTENT_TYPE_TAB,
        'pagepicker'   => self::CONTENT_TYPE_PAGEPICKER,
        'date'         => self::CONTENT_TYPE_DATE,
        'datetime'     => self::CONTENT_TYPE_DATETIME,
        'time'         => self::CONTENT_TYPE_TIME,
        'select'       => self::CONTENT_TYPE_SELECT,
        'select_table' => self::CONTENT_TYPE_SELECT_TABLE,
        'radio'        => self::CONTENT_TYPE_RADIO,
        'color'        => self::CONTENT_TYPE_COLOR,
        'custom'       => self::CONTENT_TYPE_CUSTOM,
    ];

    const CONTENT_TYPE_TEXT         = 1;
    const CONTENT_TYPE_TEXTAREA     = 2;
    const CONTENT_TYPE_INT          = 3;
    const CONTENT_TYPE_CHECKBOX     = 4;
    const CONTENT_TYPE_TINYMCE      = 5;
    const CONTENT_TYPE_IMAGE        = 6;
    const CONTENT_TYPE_FILE         = 7;
    const CONTENT_TYPE_TAB          = 8;
    const CONTENT_TYPE_PAGEPICKER   = 9;
    const CONTENT_TYPE_DATE         = 10;
    const CONTENT_TYPE_DATETIME     = 11;
    const CONTENT_TYPE_TIME         = 12;
    const CONTENT_TYPE_SELECT       = 13;
    const CONTENT_TYPE_SELECT_TABLE = 14;
    const CONTENT_TYPE_RADIO        = 15;
    const CONTENT_TYPE_COLOR        = 16;
    const CONTENT_TYPE_CUSTOM       = 17;

    const KEY_PAGE_DEFAULT   = 'default';
    const KEY_PAGE_NOT_FOUND = 'page-not-found';

    const SETTING_MAINTENANCE = 'maintenanceModeEnabled';
}