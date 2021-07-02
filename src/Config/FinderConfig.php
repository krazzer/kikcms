<?php declare(strict_types=1);


namespace KikCMS\Config;


class FinderConfig
{
    const RIGHT_NONE  = 0;
    const RIGHT_READ  = 1;
    const RIGHT_WRITE = 2;

    const MEDIA_DIR = 'media';
    const THUMB_DIR = 'thumbs';
    const FILES_DIR = 'files';

    const DEFAULT_THUMB_TYPE = 'default';

    const MAX_FILENAME_LENGTH = 255;
}