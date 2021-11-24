<?php
declare(strict_types=1);

namespace KikCMS\Classes\Phalcon\ConfigGroups;


use Phalcon\Config;

/**
 * @property string env
 * @property string defaultLanguage
 * @property string defaultCmsLanguage
 * @property string publicFolder
 * @property string cmsTitlePrefix
 * @property string baseUri
 * @property string path
 * @property string showCliOutput
 * @property bool storeMailForms
 * @property bool pageCache
 */
class ApplicationConfig extends Config
{

}