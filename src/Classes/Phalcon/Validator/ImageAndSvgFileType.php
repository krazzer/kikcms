<?php

namespace KikCMS\Classes\Phalcon\Validator;


class ImageAndSvgFileType extends FileType
{
    /** @inheritdoc */
    protected $fileTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
}