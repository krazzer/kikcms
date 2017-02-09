<?php

return [
    'delete' => 'Verwijderen',

    'cms' => [
        'loading' => 'Bezig met laden...',
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
        'products' => [
            'add'       => "Nieuw product",
            'edit'      => "Product '<i>:title</i>' bewerken",
            'delete'    => "Wilt u zeker weten deze :amount producten verwijderen?",
            'deleteOne' => "Wilt u zeker weten dit product verwijderen?",
        ],

        'subProducts' => [
            'add'       => "Nieuw sub product",
            'edit'      => "Sub product ':title' bewerken",
            'delete'    => "Wilt u zeker weten deze :amount sub producten verwijderen?",
            'deleteOne' => "Wilt u zeker weten dit sub product verwijderen?",
        ],
    ],

    'media' => [
        'title'             => 'Media',
        'button'            => [
            'upload'         => 'Uploaden',
            'uploadTitle'    => 'Upload bestanden vanaf uw computer',
            'newFolderTitle' => 'Maak een nieuwe map aan',
            'moveTitle'      => 'Geef eerder bekeken mappen weer',
            'deleteTitle'    => 'Verwijder de geselecteerde bestanden',
            'copyTitle'      => 'Kopieër de geselecteerde bestanden',
            'cutTitle'       => 'Knip de geselecteerde bestanden',
            'pasteTitle'     => 'Plak de geselecteerde bestanden',
        ],
        'searchPlaceholder' => 'Zoeken naar bestanden',
        'deleteConfirm'     => 'Wilt u zeker weten de :amount geselecteerde bestanden verwijderen?',
        'deleteConfirmOne'  => 'Wilt u zeker weten het geselecteerde bestand verwijderen?',
        'createFolder'      => 'Geef een naam op voor de nieuwe map',
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

            'passwordMismatch' => 'Wachtwoorden moeten overeenkomen',
            'csrf'             => 'Uw verzoek kon niet worden verwerkt. CSRF validatie mislukt. Probeer het opnieuw.',
            'fieldErrors'      => 'Niet alle velden zijn goed ingevuld. Controleer de velden in het rood.',
        ],

        'defaultSendLabel' => 'Versturen',
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
            'media'     => 'Media',
            'templates' => 'Templates',
            'menus'     => 'Menu\'s',
            'users'     => 'Gebruikers',
            'logout'    => 'Uitloggen',
            'form'      => 'Dataform test',
            'pages'     => "Pagina's",

            'stats' => [
                'index'   => 'Statistieken',
                'sources' => 'Bronnen',
            ],
        ],
    ],

    'error' => [
        '401' => [
            'title'       => 'Geen toegang',
            'description' => "U heeft geen toegang tot de opgevraagde pagina. \n\nHeeft u het vermoeden dat u wel toegang tot deze pagina zou moeten hebben? \nNeem dan contact op met de webmaster",
        ],

        '404' => [
            'title'       => 'Pagina niet gevonden',
            'description' => "De opgevraagde pagina bestaat niet. \nMogelijk is deze nog in ontwikkeling of is hij verwijderd. \n\nHeeft u het vermoeden dat de pagina wel zou moeten bestaan? \nNeem dan contact op met de webmaster.",
        ],

        '440' => [
            'title'       => 'Sessie verlopen',
            'description' => "Uw sessie is verlopen. U dient opnieuw in te loggen om verder te gaan.\n\nIndien uw al in een ander tabblad opnieuw bent ingelogd, dient u deze pagina te vernieuwen.",
        ],

        '500' => [
            'title'       => 'Interne fout',
            'description' => "Er is een interne fout opgetreden, de ontwikkelaars zijn op de hoogte gesteld.\n\nZij zullen het probleem zo spoedig mogelijk oplossen.",
        ],

        'unknown' => [
            'title'       => 'Onbekende fout',
            'description' => 'Er is een onbekende fout opgetreden bij uw verzoek. Probeer het later nog eens.',
        ]
    ],
];