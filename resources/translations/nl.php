<?php

return [
    'cms' => [
        'loading' => 'Bezig met laden...',
        'roles'   => [
            'developer' => 'Developer',
            'admin'     => 'Administrator',
            'user'      => 'Gebruiker',
            'client'    => 'Klant',
            'visitor'   => 'Bezoeker',
        ],
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
        'add'               => ':itemSingular toevoegen',
        'edit'              => ':itemSingular bewerken',
        'view'              => ':itemSingular bekijken',
        'searchPlaceholder' => 'Zoeken binnen resultaten',
        'save'              => 'Opslaan',
        'saveAndClose'      => 'Opslaan & sluiten',
        'closeWarning'      => 'Er zijn nog niet opgeslagen wijzigingen aangebracht, wilt u zeker weten dit venster sluiten?',
        'switchWarning'     => 'Er zijn nog niet opgeslagen wijzigingen aangebracht, wilt u zeker weten doorgaan?',
        'pickFile'          => 'Bestand kiezen',
        'sort'              => 'Volgorde slepen',
        'showAll'           => 'Toon alles',

        'pages' => [
            'total'         => 'Totaal',
            'amountPerPage' => 'Aantal items per pagina',
        ],

        'delete' => [
            'confirmOne' => ':itemSingular verwijderen?',
            'confirm'    => 'Wilt u zeker weten deze :amount :itemPlural verwijderen?',
        ],

        'deleteErrorLinked' => 'Dit item kan niet worden verwijderd, omdat het gekoppeld is elders in het systeem',
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
            'singular'        => "pagina",
            'plural'          => "pagina's",
            'addItem'         => 'Nieuw...',
            'page'            => 'Pagina',
            'menu'            => 'Menu',
            'link'            => 'Link',
            'alias'           => 'Alias',
            'urlPlaceholder'  => 'Laat leeg om een url te genereren a.d.h.v. pagina naam',
            'urlExists'       => 'De gekozen url is al in gebruik',
            'preview'         => 'Bekijk pagina',
            'titles'          => [
                'link'     => 'Deze pagina linkt door naar een andere pagina',
                'inactive' => 'Deze pagina is niet zichtbaar',
                'locked'   => 'Deze pagina is noodzakelijk voor het correct werken van de website, en kan daarom niet worden verwijderd',
            ],
            'deleteErrorFk'   => "Alleen pagina's zonder sub-pagina's kunnen worden verwijderd.",
            'linkToDesc'      => 'Linkt naar (Pagina ID of URL)',
            'urlLinkHelpText' => "Url is optioneel, en wordt alleen gebruikt voor de url generatie van sub-pagina's",
        ],

        'aliases' => [
            'singular' => "alias",
            'plural'   => "aliassen",
        ],

        'menus' => [
            'singular' => "menu",
            'plural'   => "menu's",
        ],

        'language' => [
            'singular' => "taal",
            'plural'   => "talen",
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
            'singular' => "vertaling",
            'plural'   => "vertalingen",
        ],

        'fields' => [
            'singular' => "veld",
            'plural'   => "velden",
        ],

        'templateFields' => [
            'singular' => "template veld",
            'plural'   => "template velden",
        ],

        'users' => [
            'singular' => "gebruiker",
            'plural'   => "gebruikers",

            'activationLink' => 'Genereer activatie link',
        ],
    ],

    'error' => [
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

    'fields' => [
        'active'         => 'Actief',
        'advanced'       => 'Geavanceerd',
        'allFields'      => 'Alle velden',
        'blocked'        => 'Geblokkeerd',
        'code'           => 'Code',
        'countries'      => 'Landen',
        'date'           => 'Datum',
        'description'    => 'Omschrijving',
        'email'          => 'E-mail adres',
        'file'           => 'Bestand',
        'hide'           => 'Verbergen',
        'id'             => 'Id',
        'key'            => 'Key',
        'languages'      => 'Talen',
        'multilingual'   => 'Meertalig',
        'name'           => 'Naam',
        'price'          => 'Prijs',
        'role'           => 'Rol',
        'template'       => 'Template',
        'templateFields' => 'Template velden',
        'templates'      => 'Templates',
        'translations'   => 'Vertalingen',
        'type'           => 'Type',
        'url'            => 'Url',
        'variable'       => 'Variabele',
    ],

    'global' => [
        'cancel' => 'Annuleer',
        'delete' => 'Verwijder',
        'no'     => 'Nee',
        'yes'    => 'Ja',
    ],

    'login' => [
        'logout'   => 'U bent succesvol uitgelogd',
        'failed'   => 'Onjuiste combinatie van e-mail en wachtwoord.',
        'blocked'  => 'U kunt niet inloggen omdat uw account is geblokkeerd.',
        'activate' => 'Uw account is nog niet actief, vul uw e-mail adres in om uw account te activeren.',
        'expired'  => 'U dient (opnieuw) in te loggen om verder te kunnen gaan.',

        'reset' => [
            'flash'       => 'Er is een e-mail met reset link verzonden. Let op, deze url is slechts 2 uur geldig.',
            'error'       => 'Er is iets mis gegaan bij het versturen van de reset link.',
            'buttonLabel' => 'Stuur wachtwoord reset link',

            'mail' => [
                'subject'     => 'Wachtwoord reset / aanvragen',
                'body'        => 'Druk op de onderstaande knop om uw wachtwoord (opnieuw) in te stellen. ' .
                    'Let op, deze link is slechts 2 uur geldig.',
                'buttonLabel' => 'Wachtwoord opnieuw instellen',
            ],

            'password' => [
                'flash'       => 'Uw wachtwoord is bijgewerkt. U kunt nu inloggen.',
                'hashError'   => 'Ongeldige url',
                'formMessage' => 'Voer het door u gewenste wachtwoord in',
                'expired'     => 'De geldigheid van de link is verlopen.',
            ]
        ]
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
        'deleteErrorLinked'        => 'Minimaal een van de geselecteerde bestanden kon niet worden verwijderd, ' .
            'omdat deze ergens aan gekoppeld is.',
        'createFolder'             => 'Geef een naam op voor de nieuwe map',
        'defaultFolderName'        => 'Nieuwe map',
        'editFileName'             => 'Geef een nieuwe naam op voor het bestand',
        'pickFile'                 => 'Kies bestand',
        'uploadMaxFilesWarning'    => 'U kunt maximaal :amount bestanden tegelijk uploaden',
        'uploadMaxFileSizeWarning' => 'Bestanden mogen maximaal :max zijn',
        'fileTypeWarning'          => 'Alleen bestanden met de volgende extensties zijn toegestaan: ',
        'upload'                   => [
            'error' => [
                'failed' => 'Er is iets mis gegaan bij het uploaden van :fileName',
                'mime'   => "Bestandstype ':extension' is niet toegestaan (:fileName)",
            ],
        ],
    ],

    'mailForm' => [
        'sendSuccess' => 'Het formulier is succesvol verzonden',
        'sendFail'    => 'Er is iets mis gegaan bij het verzenden van het formulier',
        'subject'     => 'Contactformulier',
    ],

    'menu' => [
        'group' => [
            'content' => 'Content',
            'stats'   => 'Statistieken',
            'cms'     => 'CMS',
        ],

        'item' => [
            'pages'     => "Pagina's",
            'media'     => 'Media',
            'templates' => 'Templates',
            'settings'  => 'Instellingen',
            'users'     => 'Gebruikers',
            'logout'    => 'Uitloggen',
            'stats'     => 'Statistieken',
        ],
    ],

    'pages' => [
        'warningTemplateChange' => 'Als u van template wisselt, gaan niet-opgeslagen wijzigingen verloren, ' .
            'wilt u zeker weten doorgaan?',
    ],

    'permissions' => [
        'editMenus' => "U kunt geen menu's bewerken",
    ],

    'statistics' => [
        'fetchingNewData' => 'Nieuwe gegevens ophalen...',
        'fetchingFailed'  => 'Ophalen van data mislukt',
        'fetchNewData'    => 'Data vernieuwen',
        'visitors'        => 'Bezoekers',
        'uniqueVisitors'  => 'Unieke bezoekers',
        'fromDate'        => 'Vanaf datum',
        'untilDate'       => 'T/m datum',
        'intervalDay'     => 'Per dag',
        'intervalMaand'   => 'Per maand',

        'overview' => [
            'totalVisits'       => 'Aantal bezoeken',
            'totalUniqueVisits' => 'Aantal unieke bezoeken',
            'dailyAverage'      => 'Gemiddeld bezoek per dag',
            'monthlyAverage'    => 'Gemiddeld bezoek per maand',
        ],

        'tab' => [
            'overview'   => 'Overzicht',
            'source'     => 'Bron',
            'page'       => "Pagina",
            'location'   => 'Locatie',
            'browser'    => 'Browser',
            'resolution' => 'Resolutie',
            'os'         => 'Besturingssysteem',
            'hits'       => 'Hits',
            'percentage' => 'Percentage',
        ],
    ],

    'system' => [
        'langCode'              => 'nl',
        'locale'                => 'nl_NL',
        'phpDateFormat'         => 'd-m-Y',
        'momentJsDateFormat'    => 'DD-MM-YYYY',
        'dateDisplayFormat'     => '%e %b %Y',
        'dateTimeDisplayFormat' => '%e %b %Y, om %H:%M',
        'monthDisplayFormat'    => '%b %Y',
        'decimalNotation'       => 'comma',
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
            'Default'           => 'De waarde van het veld :label is niet geldig',
            'FinderFileType'    => 'Het bestand mag alleen van de volgende types zijn: :types',

            'passwordMismatch' => 'Wachtwoorden moeten overeenkomen',
            'csrf'             => 'Uw verzoek kon niet worden verwerkt. CSRF validatie mislukt. Probeer het opnieuw.',
            'fieldErrors'      => 'Niet alle velden zijn goed ingevuld. Loop het formulier na op fouten of ontbrekende gegevens.',
            'slug'             => "Het veld :label mag alleen kleine letters, getallen en '-' bevatten",
        ],

        'defaultSendLabel'       => 'Versturen',
        'detachFile'             => 'Bestand loskoppelen',
        'requiredMessage'        => 'Velden met een * zijn verplicht',
        'selectPlaceHolderLabel' => 'Kies een optie...'
    ],
];
