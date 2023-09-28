<?php

return [
    'cms' => [
        'cacheManagement' => [
            'buttonLabel'   => 'Go to cache management',
            'title'         => 'Cache management',
            'memoryUsage'   => 'Memory usage',
            'uptime'        => 'Uptime',
            'hitsAndMisses' => 'Hits / misses',
            'empty'         => 'Completely clear cache',
        ],
        'roles'           => [
            'developer' => 'Developer',
            'admin'     => 'Administrator',
            'user'      => 'User',
            'client'    => 'Client',
            'visitor'   => 'Visitor',
        ],
        'loading'         => 'Loading...',
        'close'           => 'Close',
        'amount'          => 'Amount',
    ],

    'contentTypes' => [
        'text'         => 'Textfield',
        'textarea'     => 'Textarea',
        'int'          => 'Numberfield',
        'checkbox'     => 'Checkbox',
        'tinymce'      => 'TinyMCE',
        'image'        => 'Image',
        'file'         => 'File',
        'tab'          => 'Tab',
        'pagepicker'   => 'Page picker',
        'date'         => 'Date',
        'datetime'     => 'Date & time',
        'time'         => 'Time',
        'select'       => 'Select',
        'select_table' => 'Multi checkbox',
        'radio'        => 'Radiobutton',
        'color'        => 'Colorpicker',
        'custom'       => 'Custom',
    ],

    'dataForm' => [
        'saveSuccess'                  => 'Data successfully saved',
        'saveFailure'                  => 'Something went wrong when trying to save the data',
        'duplicateTemporaryKeyFailure' => 'Try to save the parent object first',
    ],

    'dataTable' => [
        'noData'            => 'No data found to display...',
        'add'               => 'Add :itemSingular',
        'edit'              => 'Edit :itemSingular',
        'view'              => 'View :itemSingular',
        'searchPlaceholder' => 'Search',
        'save'              => 'Save',
        'saveAndClose'      => 'Save & close',
        'closeWarning'      => 'Are you sure you want to close this window? There are some unsaved changes.',
        'switchWarning'     => 'Are you sure you want to proceed? There are some unsaved changes.',
        'pickFile'          => 'Pick file',
        'sort'              => 'Rearrange',
        'showAll'           => 'Show all',
        'restore'           => 'Restore lost data',
        'restoreConfirm'    => 'Do you want to overwrite the form\'s current data with the saved restore data of :date?',

        'pages' => [
            'total'         => 'Total',
            'amountPerPage' => 'Amount of items per page',
        ],

        'delete' => [
            'label'      => 'Delete :itemSingular',
            'confirmOne' => 'Delete :itemSingular?',
            'confirm'    => 'Are you sure you want to remove these :amount :itemPlural?',
            'title'      => 'Remove selected rows',
        ],

        'deleteErrorLinked' => 'This item cannot be removed, because it is used elsewhere in the system.',
    ],

    'dataTables' => [
        'default' => [
            'singular' => "item",
            'plural'   => "items",
        ],

        'products' => [
            'singular' => "product",
            'plural'   => "products",
        ],

        'pages' => [
            'singular'                => "page",
            'plural'                  => "pages",
            'addItem'                 => 'New...',
            'page'                    => 'Page',
            'menu'                    => 'Menu',
            'link'                    => 'Link',
            'alias'                   => 'Alias',
            'slugPlaceholder'         => 'Leave empty to generate a slug by page name',
            'slugExists'              => 'The chosen slug is already in use',
            'templatePageKeyMismatch' => 'The template ":template" can only be used if the key is ":key".',
            'preview'                 => 'Preview page',
            'titles'                  => [
                'link'     => 'This page links to another page',
                'inactive' => 'This page is not visible',
                'locked'   => 'This page is required for the website to work correctly, and therefore cannot be removed',
            ],
            'deleteErrorFk'           => "Only pages without sub-pages can be removed.",
            'linkToDesc'              => 'Url to where this page must link',
            'urlLinkHelpText'         => "Slug is a part of an URL, for example 'wheels' in '/products/<b>wheels</b>/wheel'. Optional.",
        ],

        'aliases' => [
            'singular' => "alias",
            'plural'   => "aliasses",
        ],

        'menus' => [
            'singular' => "menu",
            'plural'   => "menus",
        ],

        'language' => [
            'singular' => "language",
            'plural'   => "languages",
        ],

        'links' => [
            'singular' => "link",
            'plural'   => "links",
        ],

        'templates' => [
            'singular' => "template",
            'plural'   => "templates",
        ],

        'translation' => [
            'singular' => "translation",
            'plural'   => "translations",
        ],

        'fields' => [
            'singular' => "field",
            'plural'   => "fields",
        ],

        'templateFields' => [
            'singular' => "template field",
            'plural'   => "template fields",
        ],

        'users' => [
            'singular' => "user",
            'plural'   => "users",

            'activationLink' => 'Generate activation link',
            'impersonate'    => 'Login as this user',
        ],

        'mailFormSubmissions' => [
            'singular' => "submission",
            'plural'   => "submissions",
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

        '404object' => [
            'title'       => 'Object not found',
            'description' => "The requested object (:object) could not be found.",
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
        ],

        'database' => [
            'title'       => 'Lost database connection',
            'description' => 'A connection to the database could not be established. Please try again later. ' .
                'We apologise for the inconvenience.',
        ],
    ],

    'fields' => [
        'active'         => 'Active',
        'advanced'       => 'Advanced',
        'allFields'      => 'All fields',
        'blocked'        => 'Blocked',
        'code'           => 'Code',
        'countries'      => 'Countries',
        'date'           => 'Date',
        'description'    => 'Description',
        'email'          => 'E-mail address',
        'file'           => 'File',
        'hide'           => 'Hide',
        'id'             => 'Id',
        'key'            => 'Key',
        'languages'      => 'Languages',
        'multilingual'   => 'Multilingual',
        'name'           => 'Name',
        'price'          => 'Price',
        'role'           => 'Role',
        'template'       => 'Template',
        'templateFields' => 'Template fields',
        'templates'      => 'Templates',
        'translations'   => 'Translations',
        'type'           => 'Type',
        'url'            => 'Url',
        'slug'           => 'Slug',
        'variable'       => 'Variable',
    ],

    'global' => [
        'cancel'      => 'Cancel',
        'delete'      => 'Delete',
        'no'          => 'No',
        'yes'         => 'Yes',
        'subject'     => "Subject",
        'attachments' => "Attachments",
    ],

    'login' => [
        'email'        => 'E-mail address',
        'password'     => 'Password',
        'rememberMe'   => 'Remember me',
        'login'        => 'Login',
        'lostLinkText' => 'Lost your password?',
        'logout'       => 'You have been logged out',
        'failed'       => 'Incorrect combination of e-mail and password.',
        'blocked'      => 'You cannot login because your account has been blocked.',
        'expired'      => 'You need to (re)login to continue.',
        'back'         => 'Back to login screen',

        'activate' => [
            'message'     => 'Your account is not active yet, fill in your e-mail address to activate your account.',
            'buttonLabel' => 'Send account activation link',
            'title'       => 'Activate account',

            'mail' => [
                'subject'     => 'Set password',
                'body'        => 'Press the button below to set your password. This link will expire in 2 hours.',
                'buttonLabel' => 'Set password',
            ],
        ],

        'reset' => [
            'newPass'          => 'New password',
            'repeatPass'       => 'repeat password',
            'resetButtonLabel' => 'Update password',
            'flash'            => 'An e-mail with reset link has been send. This url will expire in 2 hours.',
            'error'            => 'Something went wrong sending the reset link.',
            'buttonLabel'      => 'Send password reset link',
            'title'            => 'Reset password',

            'mail' => [
                'subject'     => 'Password reset',
                'body'        => 'Press the button below to reset your password. This link will expire in 2 hours.',
                'buttonLabel' => 'Resset password',
            ],

            'password' => [
                'tokenError'  => 'Invalid token',
                'formMessage' => 'Enter your desired password',
                'expired'     => 'The URL validity has expired.',
                'space'       => 'Spaces are not allowed',
                'flash'       => [
                    'default'       => 'Your password has been updated. You may now login.',
                    'loggedIn'      => 'Your password has been updated.',
                    'loggedInOther' => 'The password for <b>:email</b> has been updated.',
                ],
            ]
        ]
    ],

    'media' => [
        'button'                   => [
            'editKey'        => 'Edit key',
            'upload'         => 'Upload',
            'uploadTitle'    => 'Upload files from your device',
            'overwrite'      => 'Overwrite',
            'overwriteTitle' => 'Overwrite the selected file with a new file from your device',
            'newFolderTitle' => 'Create a new folder',
            'moveTitle'      => 'Show earlier viewed folders',
            'deleteTitle'    => 'Delete the selected files',
            'copyTitle'      => 'Copy the selected files',
            'cutTitle'       => 'Cut the selected files',
            'pasteTitle'     => 'Paste the selected files',
            'download'       => 'Download tje geselected file',
            'rights'         => 'Edit rights of the geselected files',

            'modal' => [
                'title'               => 'Edit rights for',
                'applyToSubFolders'   => 'Apply to sub files and folders',
                'save'                => 'Save',
                'read'                => 'Read',
                'write'               => 'Edit',
                'saveError'           => 'Something went wrong updating filerights',
                'saveSuccess'         => 'The filerights have been succesfully updated',
                'intermediateWarning' => 'Saving is not possible when values are in between (-)',
                'titleMultiple'       => ':amount files',
            ],
        ],
        'title'                    => 'Media',
        'searchPlaceholder'        => 'Search files',
        'deleteConfirm'            => 'Are you sure you want to remove the :amount selected files?',
        'deleteConfirmOne'         => 'Are you sure you want to remove the selected file?',
        'deleteErrorLinked'        => 'At least one of the selected files cannot be removed because it is used elsewhere.',
        'deleteErrorLocked'        => 'At least one of the selected files hasn\'t been removed, because it is required ' .
            'for the website to work correctly.',
        'deleteErrorLinkedPage'    => 'The file :image cannot be removed, because it is used in the page \':pageName\'',
        'deleteErrorLinkedPages'   => 'The file :image cannot be removed, because it is used in the following pages: :pageNames',
        'errorCantEdit'            => 'At least one of the selected files cannot be removed because of insuffient rights',
        'createFolder'             => 'Give a name for the new folder',
        'defaultFolderName'        => 'New folder',
        'editFileName'             => 'Give a name to the file',
        'editKey'                  => 'Give a key to the file',
        'pickFile'                 => 'Pick file',
        'pickFiles'                => 'Pick files',
        'uploadMaxFilesWarning'    => 'You can upload ca maximum of :amount files at the same time',
        'uploadMaxFileSizeWarning' => 'Files cannot be bigger than :max',
        'fileTypeWarning'          => 'Only files with the following extensions are allowed: ',
        'upload'                   => [
            'error' => [
                'failed'     => 'Something went wrong uploading :fileName',
                'mime'       => "File type ':extension' is not allowed (:fileName)",
                'nameLength' => "Filename (:fileName) is too long, must be under :max characters.",
            ],
        ],
    ],

    'mailForm' => [
        'sendSuccess' => 'The form has been succesfully send',
        'sendFail'    => 'Something went wrong sending the form',
        'subject'     => 'Contactform',
    ],

    'maintenance' => [
        'checkboxLabel' => 'Enable maintenance mode',
        'title'         => 'Maintenance mode',
        'description'   => 'Currently this website is worked on behind the scenes. Please check back later.',
        'helpText'      => 'When maintenance mode is enabled, pages are no longer visible for visitors, as they will see ' .
            'a message. If you\'re logged in you will still be able to see the pages.',
    ],

    'menu' => [
        'group' => [
            'content' => 'Content',
            'stats'   => 'Statistics',
            'cms'     => 'CMS',
        ],

        'item' => [
            'pages'     => "Pages",
            'media'     => 'Media',
            'templates' => 'Templates',
            'settings'  => 'Settings',
            'users'     => 'Users',
            'logout'    => 'Logout',
            'stats'     => 'Visitors',
            'sendforms' => 'Form submissions',
        ],

        'username' => 'Logged in as: :email',
    ],

    'pages' => [
        'warningTemplateChange' => 'If you switch templates, unsaved changes are lost, do you wish to continue?',
        'slugHelpText'          => 'The slug is part of and URL, for example "services" in https://website.com/services/cms',
    ],

    'permissions' => [
        'editMenus'          => "You cannot edit menus",
        'noImpersonateAcces' => "You have insufficient rights to be able to login as this user",
        'impersonated'       => "You are now logged in as :email",
    ],

    'statistics' => [
        'fetchingNewData' => 'Retrieving new data...',
        'fetchingFailed'  => 'Retrieving new data has failed',
        'fetchNewData'    => 'Renew data',
        'visitors'        => 'Visitors',
        'uniqueVisitors'  => 'Unique visitors',
        'fromDate'        => 'From date',
        'untilDate'       => 'until date',
        'intervalDay'     => 'Per day',
        'intervalMaand'   => 'Per month',

        'overview' => [
            'totalVisits'       => 'Total visitors',
            'totalUniqueVisits' => 'Total unique visitors',
            'dailyAverage'      => 'Average visitors per day',
            'monthlyAverage'    => 'Average visitors per month',
        ],

        'tab' => [
            'overview'          => 'Overview',
            'source'            => 'Source',
            'page'              => "Page",
            'location'          => 'Location',
            'browser'           => 'Browser',
            'resolution'        => 'Resolution',
            'resolutionDesktop' => 'Resolution (desktop)',
            'resolutionTablet'  => 'Resolution (tablet)',
            'resolutionMobile'  => 'Resolution (mobiel)',
            'os'                => 'Operating system',
            'hits'              => 'Hits',
            'percentage'        => 'Percentage',
        ],
    ],

    'system' => [
        'locale'                => 'en_GB',
        'langCode'              => 'en',
        'phpDateFormat'         => 'Y-m-d',
        'phpDateTimeFormat'     => 'Y-m-d H:i',
        'dateDisplayFormat'     => '%b %e %Y',
        'dateTimeDisplayFormat' => '%b %e %Y, at %H:%M',
        'momentJsDateFormat'    => 'YYYY-MM-DD',
        'monthDisplayFormat'    => '%b %Y',
        'decimalNotation'       => 'point',
    ],

    'webform' => [
        'messages' => [
            'Alnum'               => "Field :label must contain only letters and numbers",
            'Alpha'               => "Field :label must contain only letters",
            'Between'             => "Field :label must be within the range of :min to :max",
            'Confirmation'        => "Field :label must be the same as :with",
            'Digit'               => "Field :label must be numeric",
            'Email'               => "Field :label must be an email address",
            'ExclusionIn'         => "Field :label must not be a part of list: :domain",
            'FileEmpty'           => "Field :label must not be empty",
            'FileIniSize'         => "File :label exceeds the maximum file size",
            'FileValid'           => "Field :label is not valid",
            'Identical'           => "Field :label does not have the expected value",
            'InclusionIn'         => "Field :label must be a part of list: :domain",
            'Numericality'        => "Field :label does not have a valid numeric format",
            'PresenceOf'          => "Field :label is required",
            'Regex'               => "Field :label does not match the required format",
            'PhoneNumber'         => "Field :label does not match the required format",
            'Uniqueness'          => "Field :label must be unique",
            'Url'                 => "Field :label must be a url",
            'CreditCard'          => "Field :label is not valid for a credit card number",
            'Date'                => "Field :label is not a valid date",
            'FileResolutionEqual' => "The resolution of the field :field has to be equal :resolution",
            'FileResolutionMax'   => "File :label must not exceed :resolution resolution",
            'FileResolutionMin'   => "File :label must be at least :resolution resolution",
            'FileSizeEqual'       => "File :label does not have the exact :size file size",
            'FileSizeMax'         => "File :label exceeds the size of :size",
            'FileSizeMin'         => "File :label must have the minimum size of :size",
            'FileType'            => 'File :label must be of type: :types',
            'FileMimeType'        => 'File :label must be of type: :types',
            'StringLengthMax'     => "Field :label must not exceed :max characters long",
            'StringLengthMin'     => "Field :label must be at least :min characters long",
            'Callback'            => "Field :label must match the callback function",
            'Ip'                  => "Field :label must be a valid IP address",
            'IBAN'                => "Invalid IBAN, example: GB33BANK20201555555555",

            'Default'          => 'The value of the field :label is not valid',
            'passwordMismatch' => 'Passwords must match',
            'csrf'             => 'Your request could not be processed. CSRF validation failed. Please try again.',
            'fieldErrors'      => 'Not all fields are correctly filled. Please walk through the form to check for errors.',
            'slug'             => "The field :label may only contain lowercase letters, numbers and the '-' symbol",
            'reCaptcha'        => 'Execute the ReCaptcha to prove you are not a robot',
            'reCaptchaV3Error' => 'Something went wrong determining whether you are a bot',
        ],

        'defaultSendLabel'       => 'Send',
        'detachFile'             => 'Detach file',
        'requiredMessage'        => 'Fields with a * are mandatory',
        'selectPlaceHolderLabel' => 'Choose and option...',
        'altOption'              => 'Different, namely'
    ],
];
