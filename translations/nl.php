<?php

return [
    'delete'          => 'Verwijderen',
    'cancel'          => 'Annuleer',
    'name'            => 'Naam',
    'id'              => 'Id',
    'code'            => 'Code',
    'display_order'   => 'Volgorde',
    'type'            => 'Type',
    'active'          => 'Actief',
    'advanced'        => 'Geavanceerd',
    'template'        => 'Template',
    'templates'       => 'Templates',
    'languages'       => 'Talen',
    'url'             => 'Url',
    'variable'        => 'Variabele',
    'template_fields' => 'Template velden',
    'all_fields'      => 'Alle velden',
    'file'            => 'Bestand',
    'hide'            => 'Verbergen',
    'pageNotFound'    => 'De opgevraade pagina kon niet worden gevonden...',
    'multilingual'    => 'Meertalig',
    'showAll'         => 'Toon alles',

    'cms' => [
        'loading' => 'Bezig met laden...',
    ],

    'contentTypes' => [
        'text'         => 'Tekstveld',
        'textarea'     => 'Tekstgebied',
        'int'          => 'Getalveld',
        'checkbox'     => 'Checkbox',
        'tinymce'      => 'TinyMCE',
        'image'        => 'Afbeelding',
        'file'         => 'Bestand',
        'tab'          => 'Tab',
        'pagepicker'   => 'Pagina kiezer',
        'date'         => 'Datum',
        'datetime'     => 'Datum & tijd',
        'time'         => 'Tijd',
        'select'       => 'Select',
        'select_table' => 'Multi checkbox',
        'radio'        => 'Radiobutton',
        'color'        => 'Kleurkiezer',
        'custom'       => 'Custom',
    ],

    'dataForm' => [
        'saveSuccess' => 'De gegevens zijn succesvol opgeslagen',
        'saveFailure' => 'Er is iets mis gegaan bij het opslaan van de gegevens',
    ],

    'dataTable' => [
        'noData'            => 'Er is geen data gevonden om weer te geven...',
        'add'               => 'Nieuw...',
        'edit'              => 'Bewerken',
        'searchPlaceholder' => 'Zoeken binnen resultaten',
        'save'              => 'Opslaan',
        'saveAndClose'      => 'Opslaan & sluiten',
        'closeWarning'      => 'Er zijn nog niet opgeslagen wijzigingen aangebracht, wilt u zeker weten dit venster sluiten?',
        'switchWarning'     => 'Er zijn nog niet opgeslagen wijzigingen aangebracht, wilt u zeker weten doorgaan?',
        'pickFile'          => 'Bestand kiezen',
        'sort'              => 'Volgorde slepen',

        'pages' => [
            'total'         => 'Totaal',
            'amountPerPage' => 'Aantal items per pagina',
        ],

        'delete' => [
            'confirmOne' => 'Wilt u zeker weten dit record verwijderen?',
            'confirm'    => 'Wilt u zeker weten deze :amount records verwijderen?',
        ],
    ],

    'dataTables' => [
        'default' => [
            'add'       => "Nieuw...",
            'edit'      => "Bewerken",
            'delete'    => "Wilt u zeker weten deze :amount items verwijderen?",
            'deleteOne' => "Wilt u zeker weten dit item verwijderen?",
        ],

        'products' => [
            'add'       => "Nieuw product",
            'edit'      => "Product '<i>:title</i>' bewerken",
            'delete'    => "Wilt u zeker weten deze :amount producten verwijderen?",
            'deleteOne' => "Wilt u zeker weten dit product verwijderen?",
        ],

        'pages' => [
            'add'            => "Nieuwe pagina",
            'edit'           => "Pagina '<i>:pageName</i>' bewerken",
            'delete'         => "Wilt u zeker weten deze :amount pagina's verwijderen?",
            'deleteOne'      => "Wilt u zeker weten deze pagina verwijderen?",
            'addItem'        => 'Nieuw...',
            'page'           => 'Pagina',
            'menu'           => 'Menu',
            'link'           => 'Link',
            'alias'          => 'Alias',
            'urlPlaceholder' => 'Laat leeg om een url te genereren a.d.h.v. pagina naam',
            'urlExists'      => 'De gekozen url is al in gebruik',
            'preview'        => 'Bekijk pagina',
            'titles'         => [
                'link'     => 'Deze pagina linkt door naar een andere pagina',
                'inactive' => 'Deze pagina is niet zichtbaar',
            ],
            'deleteErrorFk'  => "Alleen pagina's zonder sub-pagina's kunnen worden verwijderd.",
            'linkToDesc'     => 'Linkt naar (Pagina ID of URL)',
        ],

        'aliases' => [
            'add'  => "Nieuwe alias",
            'edit' => "Alias '<i>:name</i>' bewerken",
        ],

        'menus' => [
            'add'  => "Nieuw menu",
            'edit' => "Menu '<i>:name</i>' bewerken",
        ],

        'language' => [
            'add'       => "Nieuwe taal",
            'edit'      => "Taal '<i>:name</i>' bewerken",
            'delete'    => "Wilt u zeker weten deze :amount talen verwijderen?",
            'deleteOne' => "Wilt u zeker weten deze taal verwijderen?",
        ],

        'links' => [
            'add'  => "Nieuwe link",
            'edit' => "Link '<i>:name</i>' bewerken",
        ],

        'subProducts' => [
            'add'       => "Nieuw sub product",
            'edit'      => "Sub product '<i>:title</i>' bewerken",
            'delete'    => "Wilt u zeker weten deze :amount sub producten verwijderen?",
            'deleteOne' => "Wilt u zeker weten dit sub product verwijderen?",
        ],

        'templates' => [
            'add'       => "Nieuwe template",
            'edit'      => "Template '<i>:name</i>' bewerken",
            'delete'    => "Wilt u zeker weten deze :amount templates verwijderen?",
            'deleteOne' => "Wilt u zeker weten deze template verwijderen?",
        ],

        'fields' => [
            'add'       => "Nieuw veld",
            'edit'      => "Veld '<i>:name</i>' bewerken",
            'delete'    => "Wilt u zeker weten deze :amount velden verwijderen?",
            'deleteOne' => "Wilt u zeker weten dit veld verwijderen?",
        ],

        'templateFields' => [
            'add'       => "Nieuw template veld",
            'edit'      => "Template veld bewerken",
            'delete'    => "Wilt u zeker weten deze :amount template velden verwijderen?",
            'deleteOne' => "Wilt u zeker weten dit template veld verwijderen?",
        ],
    ],

    'media' => [
        'button'                   => [
            'upload'         => 'Uploaden',
            'uploadTitle'    => 'Upload bestanden vanaf uw computer',
            'newFolderTitle' => 'Maak een nieuwe map aan',
            'moveTitle'      => 'Geef eerder bekeken mappen weer',
            'deleteTitle'    => 'Verwijder de geselecteerde bestanden',
            'copyTitle'      => 'Kopieër de geselecteerde bestanden',
            'cutTitle'       => 'Knip de geselecteerde bestanden',
            'pasteTitle'     => 'Plak de geknipte bestanden',
        ],
        'title'                    => 'Media',
        'searchPlaceholder'        => 'Zoeken naar bestanden',
        'deleteConfirm'            => 'Wilt u zeker weten de :amount geselecteerde bestanden verwijderen?',
        'deleteConfirmOne'         => 'Wilt u zeker weten het geselecteerde bestand verwijderen?',
        'deleteErrorLinked'        => 'Minimaal een van de geselecteerde bestanden kon niet worden verwijderd, omdat deze ergens aan gekoppeld is.',
        'createFolder'             => 'Geef een naam op voor de nieuwe map',
        'defaultFolderName'        => 'Nieuwe map',
        'editFileName'             => 'Geef een nieuwe naam op voor het bestand',
        'pickFile'                 => 'Kies bestand',
        'uploadMaxFilesWarning'    => 'U kunt maximaal :amount bestanden tegelijk uploaden',
        'uploadMaxFileSizeWarning' => 'Bestanden mogen maximaal :max zijn',
        'upload'                   => [
            'error' => [
                'failed' => 'Er is iets mis gegaan bij het uploaden van :fileName',
                'mime'   => "Bestandstype ':extension' is niet toegestaan (:fileName)",
            ],
        ],
    ],

    'pages' => [
        'warningTemplateChange' => 'Als u van template wisselt, gaan niet-opgeslagen wijzigingen verloren, wilt u zeker weten doorgaan?',
    ],

    'webform' => [
        'messages' => [
            'Alnum'             => 'Het veld :label mag alleen letters en cijfers bevatten',
            'Alpha'             => 'Het veld :label mag alleen letters bevatten',
            'Between'           => 'Het veld :label mag alleen binnen een bereik van :min tot :max vallen',
            'Confirmation'      => 'Het veld :label moet hetzelfde zijn als :with',
            'Digit'             => 'Het veld :label mag alleen een getal zijn',
            'Email'             => 'Het veld :label moet een geldig e-mail adres zijn',
            'ExclusionIn'       => 'Het veld :label mag geen onderdeel zijn van list: :domain',
            'FileEmpty'         => 'Het veld :label mag niet leeg zijn',
            'FileIniSize'       => 'Het bestand :label is groter dan de maximaal toegestane grootte',
            'FileMaxResolution' => 'Het bestand :label mag geen hogere resolutie hebben dan :max',
            'FileMinResolution' => 'Het bestand :label moet tenminste een resolutie van :min hebben',
            'FileSize'          => 'Het bestand :label mag niet groter zijn dan :max',
            'FileType'          => 'Het bestand :label mag alleen van de types :types zijn',
            'FileValid'         => 'Het veld :label is niet geldig',
            'Identical'         => 'Het veld :label heeft niet de verwachte waarde',
            'InclusionIn'       => 'Het veld :label moet onderdeel zijn van: :domain',
            'Numericality'      => 'Het veld :label mag alleen numeriek zijn',
            'PresenceOf'        => 'Het veld :label is verplicht',
            'Regex'             => 'Het veld :label heeft niet het verwachte formaat',
            'TooLong'           => 'Het veld :label mag niet meer dan :max karakters lang zijn',
            'TooShort'          => 'Veld :label moet tenminste :min karakters lang zijn',
            'Uniqueness'        => 'Het veld :label bestaat al, moet uniek zijn',
            'Url'               => 'Het veld :label moet een geldige url zijn',
            'CreditCard'        => 'Het veld :label moet een geldig credit card nummer zijn',
            'Date'              => 'Het veld :label moet een geldige datum zijn',
            'NameExists'        => 'De waarde van het veld :label is niet geldig',
            'FinderFileType'    => 'Het bestand mag alleen van de volgende types zijn: :types',

            'passwordMismatch' => 'Wachtwoorden moeten overeenkomen',
            'csrf'             => 'Uw verzoek kon niet worden verwerkt. CSRF validatie mislukt. Probeer het opnieuw.',
            'fieldErrors'      => 'Niet alle velden zijn goed ingevuld. Loop het formulier na op fouten of ontbrekende gegevens.',
            'slug'             => "Het veld :label mag alleen kleine letters, getallen en '-' bevatten",
        ],

        'defaultSendLabel' => 'Versturen',
        'detachFile'       => 'Bestand loskoppelen',
    ],

    'login' => [
        'logout'   => 'U bent succesvol uitgelogd',
        'failed'   => 'Onjuiste combinatie van e-mail en wachtwoord.',
        'activate' => 'Uw account is nog niet actief, vul uw e-mail adres in om uw account te activeren.',
        'expired'  => 'U dient (opnieuw) in te loggen om verder te kunnen gaan.',

        'reset' => [
            'flash' => 'Er is een e-mail met reset link naar u verzonden, indien uw e-mail adres bekend is bij ons.',
            'error' => 'Er is iets mis gegaan bij het versturen van de reset link.',

            'mail' => [
                'subject'     => 'KikCMS wachtwoord reset',
                'body'        => 'Klik op onderstaande link om uw wachtwoord (opnieuw) in te stellen.',
                'buttonLabel' => 'Wachtwoord opnieuw instellen',
            ],

            'password' => [
                'flash'     => 'Uw wachtwoord is succesvol bijgewerkt. U kunt nu inloggen.',
                'hashError' => 'Ongeldige hash',
            ]
        ]
    ],

    'menu' => [
        'group' => [
            'content' => 'Content',
            'stats'   => 'Statistieken',
            'cms'     => 'CMS',
        ],

        'item' => [
            'pages'        => "Pagina's",
            'media'        => 'Media',
            'templates'    => 'Templates',
            'settings'     => 'Instellingen',
            'users'        => 'Gebruikers',
            'logout'       => 'Uitloggen',
            'statsIndex'   => 'Statistieken',
            'statsSources' => 'Bronnen',
        ],
    ],

    'error'  => [
        '401' => [
            'title'       => 'Geen toegang',
            'description' => "U heeft geen toegang tot de opgevraagde pagina. \n\nHeeft u het vermoeden dat u wel " .
                "toegang tot deze pagina zou moeten hebben? \nNeem dan contact op met de webmaster",
        ],

        '404' => [
            'title'       => 'Pagina niet gevonden',
            'description' => "De opgevraagde pagina bestaat niet. \nMogelijk is deze nog in ontwikkeling of is hij " .
                "verwijderd. \n\nHeeft u het vermoeden dat de pagina wel zou moeten bestaan? \nNeem dan contact op " .
                "met de webmaster.",
        ],

        '440' => [
            'title'       => 'Sessie verlopen',
            'description' => "Uw sessie is verlopen. U dient opnieuw in te loggen om verder te gaan.\n\nIndien uw al " .
                "in een ander tabblad opnieuw bent ingelogd, dient u deze pagina te vernieuwen.",
        ],

        '500' => [
            'title'       => 'Interne fout',
            'description' => "Er is een interne fout opgetreden, de ontwikkelaars zijn op de hoogte gesteld.\n\nZij " .
                "zullen het probleem zo spoedig mogelijk oplossen.",
        ],

        'unknown' => [
            'title'       => 'Onbekende fout',
            'description' => 'Er is een onbekende fout opgetreden bij uw verzoek. Probeer het later nog eens.',
        ]
    ],
    'system' => [
        'langCode'           => 'nl',
        'phpDateFormat'      => 'd-m-Y',
        'momentJsDateFormat' => 'DD-MM-YYYY',
    ],
];
