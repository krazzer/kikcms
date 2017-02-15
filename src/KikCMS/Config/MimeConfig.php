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
    ];

    /** @const array default mimeTypes allowed to upload using the Finder */
    const UPLOAD_ALLOW_DEFAULT = ['jpeg', 'jpg', 'png', 'gif', 'pdf'];
}