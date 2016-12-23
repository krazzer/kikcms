<?php

return [
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

            'passwordMismatch' => 'Wachtwoorden moeten overeenkomen',
        ],
    ],

    'login' => [
        'failed'   => 'Onjuiste combinatie van e-mail en wachtwoord.',
        'activate' => 'Uw account is nog niet actief, vul uw e-mail adres in om uw account te activeren.',
        'reset'    => [
            'flash'        => 'Er is een e-mail met reset link naar u verzonden, indien uw e-mail adres bekend is bij ons.',
            'error'        => 'Er is iets mis gegaan bij het versturen van de reset link.',
            'mail'         => [
                'subject'     => 'KikCMS wachtwoord reset',
                'body'        => 'Klik op onderstaande link om uw wachtwoord (opnieuw) in te stellen.',
                'buttonLabel' => 'Wachtwoord opnieuw instellen',
            ],
            'password'     => [
                'flash'     => 'Uw wachtwoord is succesvol bijgewerkt. U kunt nu inloggen.',
                'hashError' => 'Ongeldige hash',
            ]
        ]
    ],
];