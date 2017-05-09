<?php

return [
    'cms' => [
        'loading' => 'Bezig met laden...',
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

        'defaultSendLabel' => 'Versturen',
        'detachFile'       => 'Bestand loskoppelen',
        'requiredMessage'  => 'Velden met een * zijn verplicht',
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
