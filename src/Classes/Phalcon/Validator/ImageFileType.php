<?php

namespace KikCMS\Classes\Phalcon\Validator;


class ImageFileType extends FileType
{
    /** @inheritdoc */
    protected $fileTypes = ['jpg', 'jpeg', 'png', 'gif'];
}