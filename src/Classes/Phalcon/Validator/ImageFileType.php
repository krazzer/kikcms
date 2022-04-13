<?php declare(strict_types=1);

namespace KikCMS\Classes\Phalcon\Validator;


use KikCMS\Config\FinderConfig;

class ImageFileType extends FileType
{
    /** @inheritdoc */
    protected array $fileTypes = FinderConfig::FILE_TYPES_IMAGE;
}