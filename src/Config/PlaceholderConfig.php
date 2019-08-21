<?php declare(strict_types=1);


namespace KikCMS\Config;


use KikCMS\ObjectLists\PlaceholderFileThumbUrlMap;
use KikCMS\ObjectLists\PlaceholderFileUrlMap;
use KikCMS\Objects\PlaceholderFileThumbUrl;
use KikCMS\Objects\PlaceholderFileUrl;

class PlaceholderConfig
{
    const FILE_URL       = 'fileUrl';
    const FILE_THUMB_URL = 'fileThumbUrl';

    const CLASS_MAP = [
        self::FILE_URL       => PlaceholderFileUrl::class,
        self::FILE_THUMB_URL => PlaceholderFileThumbUrl::class,
    ];

    const MAP_CLASS_MAP = [
        self::FILE_URL       => PlaceholderFileUrlMap::class,
        self::FILE_THUMB_URL => PlaceholderFileThumbUrlMap::class,
    ];
}