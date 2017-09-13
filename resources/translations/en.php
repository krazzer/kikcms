<?php

return [
    'cms' => [
        'loading' => 'Loading...',
    ],

    'dataForm' => [
        'saveSuccess' => 'Data successfully saved',
        'saveFailure' => 'Something went wrong when trying to save the data',
    ],

    'dataTable' => [
        'noData'            => 'No data found to display...',
        'add'               => 'Add :itemSingular',
        'edit'              => 'Edit :itemSingular',
        'searchPlaceholder' => 'Search',
        'save'              => 'Save',
        'saveAndClose'      => 'Save & close',
        'closeWarning'      => 'Are you sure you want to close this window? There are some unsaved changes.',
        'switchWarning'     => 'Are you sure you want to proceed? There are some unsaved changes.',
        'pickFile'          => 'Pick file',
        'sort'              => 'Rearrange',
        'showAll'           => 'Show all',

        'pages' => [
            'total'         => 'Total',
            'amountPerPage' => 'Amount of items per page',
        ],

        'delete' => [
            'confirmOne' => 'Are you sure you want to delete this :itemSingular?',
            'confirm'    => 'Are you sure you want to delete these :amount :itemPlural?',
        ],
    ],

    'error' => [
        '401' => [
            'title'       => 'Unauthorized',
            'description' => "You are not authorized to view this page.",
        ],

        '404' => [
            'title'       => 'Page not found',
            'description' => "The requested page could not be found",
        ],

        '440' => [
            'title'       => 'Session expired',
            'description' => "Your session has expired. You need to login again to continue.",
        ],

        '500' => [
            'title'       => 'Internal error',
            'description' => "An internal error has occurred. Support services have been notified.",
        ],

        'unknown' => [
            'title'       => 'Unknown error',
            'description' => 'An unknown error has occurred. Please try again later.',
        ]
    ],

    'fields' => [
        'price' => 'Prijs',
    ],

    'global' => [
        'cancel' => 'Cancel',
        'delete' => 'Delete',
    ],

    'pages' => [
        'warningTemplateChange' => 'If you switch template, unsaved changes will not be saved, do you want to continue?',
    ],

    'system' => [
        'langCode'              => 'en',
        'phpDateFormat'         => 'Y-m-d',
        'dateDisplayFormat'     => '%b %e %Y',
        'dateTimeDisplayFormat' => '%b %e %Y, at %H:%M',
        'momentJsDateFormat'    => 'YYYY-MM-DD',
        'decimalNotation'       => 'point',
    ],

    'webform' => [
        'messages' => [
            'Alnum'             => "Field :label must contain only letters and numbers",
            'Alpha'             => "Field :label must contain only letters",
            'Between'           => "Field :label must be within the range of :min to :max",
            'Confirmation'      => "Field :label must be the same as :with",
            'Digit'             => "Field :label must be numeric",
            'Email'             => "Field :label must be an email address",
            'ExclusionIn'       => "Field :label must not be a part of list: :domain",
            'FileEmpty'         => "Field :label must not be empty",
            'FileIniSize'       => "File :label exceeds the maximum file size",
            'FileMaxResolution' => "File :label must not exceed :max resolution",
            'FileMinResolution' => "File :label must be at least :min resolution",
            'FileSize'          => "File :label exceeds the size of :max",
            'FileType'          => "File :label must be of type: :types",
            'FileValid'         => "Field :label is not valid",
            'Identical'         => "Field :label does not have the expected value",
            'InclusionIn'       => "Field :label must be a part of list: :domain",
            'Numericality'      => "Field :label does not have a valid numeric format",
            'PresenceOf'        => "Field :label is required",
            'Regex'             => "Field :label does not match the required format",
            'TooLong'           => "Field :label must not exceed :max characters long",
            'TooShort'          => "Field :label must be at least :min characters long",
            'Uniqueness'        => "Field :label must be unique",
            'Url'               => "Field :label must be a url",
            'CreditCard'        => "Field :label is not valid for a credit card number",
            'Date'              => "Field :label is not a valid date",

            'Default'          => 'The value of the field :label is not valid',
            'FinderFileType'   => 'The file may only be one of the following types: :types',
            'passwordMismatch' => 'Passwords must match',
            'csrf'             => 'Your request could not be processed. CSRF validation failed. Please try again.',
            'fieldErrors'      => 'Not all fields are correctly filled. Please walk through the form to check for errors.',
            'slug'             => "The field :label may only contain lowercase letters, numbers and the '-' symbol",
        ],

        'defaultSendLabel' => 'Send',
        'detachFile'       => 'Detach file',
        'requiredMessage'  => 'Fields with a * are mandatory',
    ],
];
