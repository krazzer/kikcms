<?php declare(strict_types=1);

namespace KikCMS\Classes\Phalcon\Validator;


class ImageAndSvgFileType extends FileType
{
    /** @inheritdoc */
    protected array $fileTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
}