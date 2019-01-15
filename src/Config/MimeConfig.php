<?php

namespace KikCMS\Config;


class MimeConfig
{
    /** @const array contains all mimeTypes, extend when necessary */
    const ALL_MIME_TYPES = [
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'jpg'  => ['image/jpeg', 'image/pjpeg'],
        'png'  => ['image/png'],
        'gif'  => ['image/gif'],
        'pdf'  => ['application/pdf', 'image/pjpeg'],
        'doc'  => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls'  => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'ppt'  => ['application/vnd.ms-powerpoint'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'svg'  => ['image/svg+xml', 'text/plain'],
    ];

    /** @const array default mimeTypes allowed to upload using the Finder */
    const UPLOAD_ALLOW_DEFAULT = ['jpeg', 'jpg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'svg'];
}