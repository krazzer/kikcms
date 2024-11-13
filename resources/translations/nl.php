<?php

return [
    'cms' => [
        'cacheManagement' => [
            'buttonLabel'   => 'Ga naar cache beheer',
            'title'         => 'Cache beheer',
            'memoryUsage'   => 'Geheugen gebruik',
            'uptime'        => 'Uptime',
            'hitsAndMisses' => 'Hits / misses',
            'empty'         => 'Volledige cache legen',
        ],
        'roles'           => [
            'developer' => 'Developer',
            'admin'     => 'Administrator',
            'user'      => 'Gebruiker',
            'client'    => 'Klant',
            'visitor'   => 'Bezoeker',
        ],
        'loading'         => 'Bezig met laden...',
        'close'           => 'Sluiten',
        'amount'          => 'Aantal',
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
        'saveSuccess'                  => 'De gegevens zijn succesvol opgeslagen',
        'saveFailure'                  => 'Er is iets mis gegaan bij het opslaan van de gegevens',
        'duplicateTemporaryKeyFailure' => 'Probeer eerst het bovenliggende onderdeel op te slaan, en probeer het daarna nog eens',
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
        'restore'           => 'Herstel verloren data',
        'restoreConfirm'    => 'Wil je de data van het huidige formulier overschrijven met de opgeslagen herstel data van :date?',

        'pages' => [
            'total'         => 'Totaal',
            'amountPerPage' => 'Aantal items per pagina',

            'seo' => [
                'title'           => 'SEO titel',
                'keywords'        => 'SEO sleutelwoorden',
                'description'     => 'SEO omschrijving',
                'titleHelp'       => 'De SEO titel is de naam van de pagina zoals je hem zou willen weergeven in ' .
                    'zoekmachines (indien afwijkend van de reguliere titel)',
                'keywordsHelp'    => 'Sleutelwoorden zijn de woorden (kommagescheiden) waarop je wilt dat de ' .
                    'pagina gevonden moet worden in zoekmachines.',
                'descriptionHelp' => 'De omschrijving is de omschrijving die je wilt tonen van de pagina in de ' .
                    'zoekmachine. Als je dit leeg laat zal de zoekmachine dit zelf a.d.h.v. van de inhoud van de ' .
                    'pagina',
            ]
        ],

        'delete' => [
            'label'      => ':itemSingular verwijderen',
            'confirmOne' => ':itemSingular verwijderen?',
            'confirm'    => 'Wilt u zeker weten deze :amount :itemPlural verwijderen?',
            'title'      => 'Geselecteerde rijen verwijderen',
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
            'plural'   => "producten",
        ],

        'pages' => [
            'singular'                => "pagina",
            'plural'                  => "pagina's",
            'addItem'                 => 'Nieuw...',
            'page'                    => 'Pagina',
            'menu'                    => 'Menu',
            'link'                    => 'Link',
            'alias'                   => 'Alias',
            'slugPlaceholder'         => 'Laat leeg om een slug te genereren a.d.h.v. pagina naam',
            'templatePageKeyMismatch' => 'De template ":template" kan alleen worden gebruikt als de key ":key" is.',
            'slugExists'              => 'De gekozen slug is al in gebruik',
            'preview'                 => 'Bekijk pagina',
            'titles'                  => [
                'link'     => 'Deze pagina linkt door naar een andere pagina',
                'inactive' => 'Deze pagina is niet zichtbaar',
                'locked'   => 'Deze pagina is noodzakelijk voor het correct werken van de website, en kan daarom niet worden verwijderd',
            ],
            'deleteErrorFk'           => "Alleen pagina's zonder sub-pagina's kunnen worden verwijderd.",
            'linkToDesc'              => 'Url waar de pagina naar toe linkt',
            'urlLinkHelpText'         => "Slug onderdeel is het gedeelte van een URL, bijv. 'wielen' in '/producten/<b>wielen</b>/wiel'. Optioneel.",
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
            'impersonate'    => 'Login als deze gebruiker',
        ],

        'mailFormSubmissions' => [
            'singular' => "inzending",
            'plural'   => "inzendingen",
        ],
    ],

    'error' => [
        '401' => [
            'title'       => 'Geen toegang',
            'description' => "U heeft geen toegang tot de opgevraagde pagina. \n\nHeeft u het vermoeden dat u wel " .
                "toegang tot deze pagina zou moeten hebben? \nNeem dan contact op met de ontwikkelaar",
        ],

        '404' => [
            'title'       => 'Pagina niet gevonden',
            'description' => "De opgevraagde pagina bestaat niet. \nMogelijk is deze nog in ontwikkeling of is hij " .
                "verwijderd. \n\nHeeft u het vermoeden dat de pagina wel zou moeten bestaan? \nNeem dan contact op " .
                "met de ontwikkelaar.",
        ],

        '404object' => [
            'title'       => 'Object niet gevonden',
            'description' => "Het opgevraagde object (:object) kon niet worden gevonden.",
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
        ],

        'database' => [
            'title'       => 'Geen database verbinding',
            'description' => 'Er kon geen connectie gemaakt worden met de database. Probeer het later nog eens. ' .
                'Excuses voor het ongemak.',
        ],
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
        'slug'           => 'Slug',
        'variable'       => 'Variabele',
    ],

    'global' => [
        'cancel'      => 'Annuleer',
        'delete'      => 'Verwijder',
        'no'          => 'Nee',
        'yes'         => 'Ja',
        'subject'     => "Onderwerp",
        'attachments' => "Bijlagen",
    ],

    'login' => [
        'email'        => 'E-mail adres',
        'password'     => 'Wachtwoord',
        'rememberMe'   => 'Onthoud mij',
        'login'        => 'Inloggen',
        'lostLinkText' => 'Wachtwoord vergeten?',
        'logout'       => 'U bent succesvol uitgelogd',
        'failed'       => 'Onjuiste combinatie van e-mail en wachtwoord.',
        'blocked'      => 'U kunt niet inloggen omdat uw account is geblokkeerd.',
        'expired'      => 'U dient (opnieuw) in te loggen om verder te kunnen gaan.',
        'back'         => 'Terug naar login scherm',

        'activate' => [
            'message'     => 'Uw account is nog niet actief, vul uw e-mail adres in om uw account te activeren.',
            'buttonLabel' => 'Stuur account activatie link',
            'title'       => 'Account activeren',

            'mail' => [
                'subject'     => 'Wachtwoord instellen',
                'body'        => 'Druk op de onderstaande knop om uw wachtwoord in te stellen. ' .
                    'Let op, deze link is slechts 2 uur geldig.',
                'buttonLabel' => 'Wachtwoord instellen',
            ],
        ],

        'reset' => [
            'newPass'          => 'Nieuw wachtwoord',
            'repeatPass'       => 'Herhaal wachtwoord',
            'resetButtonLabel' => 'Nieuw wachtwoord instellen',
            'flash'            => 'Er is een e-mail met reset link verzonden. Let op, deze url is slechts 2 uur geldig.',
            'error'            => 'Er is iets mis gegaan bij het versturen van de reset link.',
            'buttonLabel'      => 'Stuur wachtwoord reset link',
            'title'            => 'Wachtwoord resetten',

            'mail' => [
                'subject'     => 'Wachtwoord resetten',
                'body'        => 'Druk op de onderstaande knop om uw wachtwoord opnieuw in te stellen. ' .
                    'Let op, deze link is slechts 2 uur geldig.',
                'buttonLabel' => 'Wachtwoord opnieuw instellen',
            ],

            'password' => [
                'tokenError'  => 'Ongeldige token',
                'formMessage' => 'Voer het door u gewenste wachtwoord in',
                'expired'     => 'De geldigheid van de link is verlopen.',
                'space'       => 'Spaties zijn niet toegestaan',
                'flash'       => [
                    'default'       => 'Uw wachtwoord is bijgewerkt. U kunt nu inloggen.',
                    'loggedIn'      => 'Uw wachtwoord is bijgewerkt.',
                    'loggedInOther' => 'Het wachtwoord voor <b>:email</b> is bijgewerkt.',
                ],
            ]
        ]
    ],

    'media' => [
        'button'                   => [
            'editKey'        => 'Key bewerken',
            'upload'         => 'Uploaden',
            'uploadTitle'    => 'Upload bestanden vanaf uw computer',
            'overwrite'      => 'Overschrijf',
            'overwriteTitle' => 'Overschrijf het geselecteerde bestand met een nieuw bestand vanaf uw computer',
            'newFolderTitle' => 'Maak een nieuwe map aan',
            'moveTitle'      => 'Geef eerder bekeken mappen weer',
            'deleteTitle'    => 'Verwijder de geselecteerde bestanden',
            'copyTitle'      => 'Kopieër de geselecteerde bestanden',
            'cutTitle'       => 'Knip de geselecteerde bestanden',
            'pasteTitle'     => 'Plak de geknipte bestanden',
            'download'       => 'Download het geselecteerde bestand',
            'rights'         => 'Beheer rechten van de geselecteerde bestanden',

            'modal' => [
                'title'               => 'Rechten instellen voor',
                'applyToSubFolders'   => 'Pas toe op onderliggende bestanden en mappen',
                'save'                => 'Opslaan',
                'read'                => 'Lezen',
                'write'               => 'Schrijven',
                'saveError'           => 'Er is iets mis gegaan tijdens het bijwerken van de bestandsrechten',
                'saveSuccess'         => 'De bestandsrechten zijn succesvol bijgewerkt',
                'intermediateWarning' => 'Opslaan is niet mogelijk indien er waardes tussenliggend (-) zijn',
                'titleMultiple'       => ':amount bestanden',
            ],
        ],
        'title'                    => 'Media',
        'searchPlaceholder'        => 'Zoeken naar bestanden',
        'deleteConfirm'            => 'Wilt u zeker weten de :amount geselecteerde bestanden verwijderen?',
        'deleteConfirmOne'         => 'Wilt u zeker weten het geselecteerde bestand verwijderen?',
        'deleteErrorLinked'        => 'Minimaal een van de geselecteerde bestanden kon niet worden verwijderd, ' .
            'omdat deze ergens aan gekoppeld is.',
        'deleteErrorLocked'        => 'Minimaal 1 van de geselecteerde bestanden is niet verwijderd, omdat deze nodig ' .
            'is voor het correct werken van de website.',
        'deleteErrorLinkedPage'    => 'De afbeelding :image kan niet worden verwijderd, omdat deze gebruikt wordt in de pagina \':pageName\'',
        'deleteErrorLinkedPages'   => 'De afbeelding :image kan niet worden verwijderd, omdat deze gebruikt wordt in de volgende pagina\'s: :pageNames',
        'errorCantEdit'            => 'Minimaal 1 van de geselecteerde bestanden is niet verwijderd, omdat u deze niet mag bewerken',
        'createFolder'             => 'Geef een naam op voor de nieuwe map',
        'defaultFolderName'        => 'Nieuwe map',
        'editFileName'             => 'Geef een nieuwe naam op voor het bestand',
        'editKey'                  => 'Geef een nieuwe key op voor het bestand',
        'pickFile'                 => 'Kies bestand',
        'pickFiles'                => 'Kies bestanden',
        'uploadMaxFilesWarning'    => 'U kunt maximaal :amount bestanden tegelijk uploaden',
        'uploadMaxFileSizeWarning' => 'Bestanden mogen maximaal :max zijn',
        'fileTypeWarning'          => 'Alleen bestanden met de volgende extensties zijn toegestaan: ',
        'upload'                   => [
            'error' => [
                'failed'     => 'Er is iets mis gegaan bij het uploaden van :fileName',
                'mime'       => "Bestandstype ':extension' is niet toegestaan (:fileName)",
                'nameLength' => "Bestandsnaam (:fileName) is te lang, max maximaal :max karakters zijn.",
            ],
        ],
    ],

    'mailForm' => [
        'sendSuccess' => 'Het formulier is succesvol verzonden',
        'sendFail'    => 'Er is iets mis gegaan bij het verzenden van het formulier',
        'subject'     => 'Contactformulier',
    ],

    'maintenance' => [
        'checkboxLabel' => 'Onderhouds modus inschakelen',
        'title'         => 'Onderhouds modus',
        'description'   => 'Er wordt momenteel achter de schermen aan deze website gewerkt. Probeer het later nog eens.',
        'helpText'      => 'Als onderhouds modus is ingeschakeld, zijn pagina\'s niet meer te bezoeken. Bezoekers ' .
            'krijgen een melding te zien. Als je bent ingelogd in het CMS kun je de website nog wel bekijken',
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
            'sendforms' => 'Formulier inzendingen',
            'users'     => 'Gebruikers',
            'logout'    => 'Uitloggen',
            'stats'     => 'Bezoekers',
        ],

        'username' => 'Ingelogd als: :email',
    ],

    'pages' => [
        'warningTemplateChange' => 'Als u van template wisselt, gaan niet-opgeslagen wijzigingen verloren, ' .
            'wilt u zeker weten doorgaan?',
        'slugHelpText'          => 'De slug is een deel van een URL, bijvoorbeeld "diensten" in https://website.nl/diensten/cms',
    ],

    'permissions' => [
        'editMenus'          => "U kunt geen menu's bewerken",
        'noImpersonateAcces' => "U heeft onvoldoende rechten om als deze gebruiker te kunnen inloggen",
        'impersonated'       => "U bent nu ingelogd als :email",
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
            'overview'          => 'Overzicht',
            'source'            => 'Bron',
            'page'              => "Pagina",
            'location'          => 'Locatie',
            'browser'           => 'Browser',
            'resolution'        => 'Resolutie',
            'resolutionDesktop' => 'Resolutie (desktop)',
            'resolutionTablet'  => 'Resolutie (tablet)',
            'resolutionMobile'  => 'Resolutie (mobiel)',
            'os'                => 'Besturingssysteem',
            'hits'              => 'Hits',
            'percentage'        => 'Percentage',
        ],
    ],

    'system' => [
        'langCode'              => 'nl',
        'locale'                => 'nl_NL',
        'phpDateFormat'         => 'd-m-Y',
        'phpDateTimeFormat'     => 'd-m-Y H:i',
        'momentJsDateFormat'    => 'DD-MM-YYYY',
        'dateDisplayFormat'     => 'd MMM Y',
        'dateTimeDisplayFormat' => '%e %b %Y, \'om\' %H:%M',
        'monthDisplayFormat'    => '%b %Y',
        'decimalNotation'       => 'comma',
    ],

    'webform' => [
        'messages' => [
            'Alnum'               => 'Het veld :label mag alleen letters en cijfers bevatten',
            'Alpha'               => 'Het veld :label mag alleen letters bevatten',
            'Between'             => 'Het veld :label mag alleen binnen een bereik van :min tot :max vallen',
            'Confirmation'        => 'Het veld :label moet hetzelfde zijn als :with',
            'Digit'               => 'Het veld :label mag alleen een getal zijn',
            'Email'               => 'Het veld :label moet een geldig e-mail adres zijn',
            'ExclusionIn'         => 'Het veld :label mag geen onderdeel zijn van list: :domain',
            'FileEmpty'           => 'Het veld :label mag niet leeg zijn',
            'FileIniSize'         => 'Het bestand :label is groter dan de maximaal toegestane grootte',
            'FileValid'           => 'Het veld :label is niet geldig',
            'Identical'           => 'Het veld :label heeft niet de verwachte waarde',
            'InclusionIn'         => 'Het veld :label moet onderdeel zijn van: :domain',
            'Numericality'        => 'Het veld :label mag alleen numeriek zijn',
            'PresenceOf'          => 'Het veld :label is verplicht',
            'Regex'               => 'Het veld :label heeft niet het verwachte format',
            'PhoneNumber'         => "Het veld :label heeft niet het verwachte format",
            'Uniqueness'          => 'Het veld :label bestaat al, moet uniek zijn',
            'Url'                 => 'Het veld :label moet een geldige url zijn',
            'CreditCard'          => 'Het veld :label moet een geldig credit card nummer zijn',
            'Date'                => 'Het veld :label moet een geldige datum zijn',
            'FileResolutionEqual' => "Het bestand :label mag geen hogere resolutie hebben dan :resolution",
            'FileResolutionMax'   => "Het bestand :label mag geen hogere resolutie hebben dan :resolution",
            'FileResolutionMin'   => "Het bestand :label moet tenminste een resolutie van :resolution hebben",
            'FileSizeEqual'       => "Het bestand :label moet exact :size groot zijn",
            'FileSizeMax'         => "Het bestand :label mag niet groter zijn dan :size",
            'FileSizeMin'         => "Het bestand :label mag niet kleiner zijn dan :size",
            'FileType'            => 'Het bestand :label moet één van de volgende types zijn: :types',
            'FileMimeType'        => 'Het bestand :label moet één van de volgende types zijn: :types',
            'StringLengthMax'     => "Het veld :label mag niet meer dan :max karakters lang zijn",
            'StringLengthMin'     => "Veld :label moet tenminste :min karakters lang zijn",
            'Callback'            => "Het veld :label moet gelijk zijn aan de callback functie",
            'Ip'                  => "Het veld :label moet een geldig IP adres zijn",
            'IBAN'                => "Ongeldige IBAN, voorbeeld: NL99BANK1234567890",

            'Default'          => 'De waarde van het veld :label is niet geldig',
            'passwordMismatch' => 'Wachtwoorden moeten overeenkomen',
            'csrf'             => 'Uw verzoek kon niet worden verwerkt. CSRF validatie mislukt. Probeer het opnieuw.',
            'fieldErrors'      => 'Niet alle velden zijn goed ingevuld. Loop het formulier na op fouten of ontbrekende gegevens.',
            'slug'             => "Het veld :label mag alleen kleine letters, getallen en '-' bevatten",
            'reCaptcha'        => 'Voer de ReCaptcha uit om te bewijzen dat u geen robot bent',
            'reCaptchaV3Error' => 'Er is iets mis gegaan bij de verificatie of u geen robot bent',
        ],

        'defaultSendLabel'       => 'Versturen',
        'detachFile'             => 'Bestand loskoppelen',
        'requiredMessage'        => 'Velden met een * zijn verplicht',
        'selectPlaceHolderLabel' => 'Kies een optie...',
        'altOption'              => 'Anders, namelijk'
    ],

    'spamBlock' => [
        "q1"  => "Welke kleur heeft de heldere lucht?",
        "q2"  => "Welke kleur heeft gras?",
        "q3"  => "Welke kleur heeft een banaan?",
        "q4"  => "Welke kleur hebben sinaasappels?",
        "q5"  => "Welke kleur heeft een tomaat?",
        "q6"  => "Welke kleur heeft sneeuw?",
        "q7"  => "Welke kleur krijg je als je rood en wit mengt?",
        "q8"  => "Welke kleur heeft een citroen?",
        "q9"  => "Welke kleur heeft chocolade?",
        "q10" => "Welke kleur heeft een aardbei?",
        "q11" => "Welke kleur heeft een flamingo?",
        "q12" => "Welke kleur heeft de zon?",
        "q13" => "Welke kleur krijg je als je blauw en rood mengt?",
        "q14" => "Welke kleur heeft sla?",
        "q15" => "Welke kleur heeft een wortel?",
        "q16" => "Welke kleur hebben rozen?",
        "q17" => "Welke kleur hebben bladeren in de herfst?",
        "q18" => "Welke kleur krijg je als je geel en blauw mengt?",
        "q19" => "Welke kleur heeft een walnoot?",
        "q20" => "Welke kleur hebben kersen?",
        "q21" => "Welke kleur hebben blauwe bessen?",
        "q22" => "Welke kleur hebben krokodillen?",
        "q23" => "Welke kleur heeft melk?",
        "q24" => "Welke kleur heeft een tennisbal?",
        "q25" => "Welke kleur heeft een brandweerwagen?",
        "q26" => "Welke kleur heeft ketchup?",
        "q27" => "Welke kleur heeft een herfstblad?",
        "q28" => "Welke kleur heeft een avocado?",
        "q29" => "Welke kleur heeft de ruimte?",
        "q30" => "Welke kleur heeft een lieveheersbeestje?",

        "blue"   => "blauw",
        "green"  => "groen",
        "yellow" => "geel",
        "orange" => "oranje",
        "red"    => "rood",
        "white"  => "wit",
        "pink"   => "roze",
        "brown"  => "bruin",
        "purple" => "paars",
        "black"  => "zwart",

        "message"  => "Verkeerd antwoord",
    ],
];
