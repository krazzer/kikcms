<?php

namespace KikCMS\Config;


use KikCMS\Models\DummyProducts;

class FinderConfig
{
    const FILTER_SEARCH    = 'search';
    const FILTER_FOLDER_ID = 'folderId';

    const RELATION_KEYS = [
        'testProduct' => [
            'model' => DummyProducts::class,
            'field' => DummyProducts::FIELD_IMAGE_ID
        ],
    ];
}