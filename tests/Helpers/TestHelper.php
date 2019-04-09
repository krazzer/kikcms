<?php


namespace Helpers;


use KikCMS\Classes\Frontend\Extendables\MediaResizeBase;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Classes\ImageHandler\ImageHandler;
use KikCMS\Classes\ObjectStorage\File;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\Twig;
use KikCMS\Classes\Phalcon\View;
use KikCMS\Classes\Translator;
use KikCMS\Config\TranslatorConfig;
use KikCMS\ObjectLists\CmsPluginList;
use KikCMS\Services\CacheService;
use KikCMS\Services\DataTable\DataTableFilterService;
use KikCMS\Services\Finder\FileService;
use KikCMS\Services\LanguageService;
use KikCMS\Services\ModelService;
use KikCMS\Services\TwigService;
use KikCMS\Services\WebForm\RelationKeyService;
use KikCmsCore\Services\DbService;
use Phalcon\Cache\Frontend\Data;
use Phalcon\Cache\Frontend\Json;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di;
use Phalcon\DiInterface;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaData\Memory;
use Phalcon\Mvc\Url;
use Phalcon\Session\Bag;
use Phalcon\Validation;
use PHPUnit\Framework\TestCase;

class TestHelper extends TestCase
{
    /** @var DiInterface */
    private $testDbDi;

    public function testGetterAndSetter(string $className, array $variables)
    {
        foreach ($variables as $variable) {
            $setter = 'set' . ucfirst($variable);
            $getter = 'get' . ucfirst($variable);

            // test getter
            $class = new $className();
            $class->$setter('test');

            $this->assertEquals('test', $class->$getter());

            // test setter
            $class = new $className();

            $classReturned = $class->$setter('test');

            $this->assertEquals('test', $class->$getter());
            $this->assertEquals($classReturned, $class);
        }
    }

    /**
     * Gets a fully operational translator, to automatically test if the requested translation keys exists
     *
     * @return Translator
     */
    public function getTranslator(): Translator
    {
        $cacheServiceMock = $this->getMockBuilder(CacheService::class)
            ->setMethods(['cache'])
            ->getMock();

        $websiteSettingsMock = $this->getMockBuilder(WebsiteSettingsBase::class)
            ->setMethods(['getPluginList'])
            ->getMock();

        $cacheServiceMock->method('cache')->willReturn([]);
        $websiteSettingsMock->method('getPluginList')->willReturn(new CmsPluginList);

        $translatorMock = new Translator([
            TranslatorConfig::LANGUAGE_NL => dirname(dirname(__DIR__)) . '/resources/translations/nl.php',
            TranslatorConfig::LANGUAGE_EN => dirname(dirname(__DIR__)) . '/resources/translations/en.php',
        ]);

        $translatorMock->setLanguageCode('nl');

        $translatorMock->cache           = null;
        $translatorMock->cacheService    = $cacheServiceMock;
        $translatorMock->websiteSettings = $websiteSettingsMock;

        return $translatorMock;
    }

    /**
     * @return DiInterface
     */
    public function getTestDi(): DiInterface
    {
        if ($this->testDbDi) {
            return $this->testDbDi;
        }

        // set session superglobal
        if ( ! isset($_SESSION)) $_SESSION = [];

        $di = new Di\FactoryDefault();

        $dbConfig = [
            'username' => 'root',
            'password' => 'adminkik12',
            'dbname'   => 'test',
            'host'     => '127.0.0.1',
            'charset'  => 'utf8mb4',
        ];

        // use cms default config
        $config = new Ini(dirname(dirname(__DIR__)) . '/config/config.ini');

        $config->application->path = $this->getSitePath();

        $fileStorage = new File();
        $fileStorage->setStorageDir($this->getSitePath() . 'storage/');

        $frontend = new Json(["lifetime" => 3600 * 24 * 365 * 1000]);
        $keyValue = new \Phalcon\Cache\Backend\File($frontend, ['cacheDir' => $this->getSitePath() . 'storage/keyvalue/']);

        $url = new Url();
        $url->setBaseUri('/');

        $permission = new Permission();

        $di->set('languageService', new LanguageService);
        $di->set('modelsManager', new Manager);
        $di->set('modelsMetadata', new Memory);
        $di->set('imageHandler', new ImageHandler);
        $di->set('dbService', new DbService);
        $di->set('mediaResize', new MediaResizeBase);
        $di->set('cacheService', new CacheService);
        $di->set('validation', new Validation);
        $di->set('websiteSettings', new WebsiteSettingsBase);
        $di->set('twigService', new TwigService('', ''));
        $di->set('modelService', new ModelService);
        $di->set('relationKeyService', new RelationKeyService);
        $di->set('dataTableFilterService', new DataTableFilterService);
        $di->set('fileService', new FileService('media', 'thumbs'));
        $di->set('cache', new \Phalcon\Cache\Backend\Memory(new Data));
        $di->set('translator', $this->getTranslator());
        $di->set('db', new Mysql($dbConfig));
        $di->set('config', $config);
        $di->set('keyValue', $keyValue);
        $di->set('fileStorage', $fileStorage);
        $di->set('url', $url);
        $di->set('permisson', $permission);
        $di->set('persistent', new Bag('persistent'));
        $di->set('sessionBag', new Bag('session'));
        $di->set('view', $this->getView());

        $permission->setDI($di);

        $di->set('acl', $permission->getAcl());

        Di::setDefault($di);

        $this->testDbDi = $di;

        return $this->testDbDi;
    }

    /**
     * @return string
     */
    public function getTestFilesPath(): string
    {
        return dirname(__DIR__) . '/TestSitePath/';
    }

    /**
     * @return View
     */
    private function getView(): View
    {
        $cmsViewDir     = dirname(dirname(__DIR__)) . '/src/Views/';
        $cmsResourceDir = dirname(dirname(__DIR__)) . '/resources/';

        $namespaces = [
            'kikcms'       => $cmsViewDir,
            'cmsResources' => $cmsResourceDir,
        ];

        $view = new View();
        $view->setViewsDir($cmsViewDir);
        $view->setNamespaces($namespaces);
        $view->registerEngines([
            Twig::DEFAULT_EXTENSION => function (View $view, DiInterface $di) {
                $options = [
                    'cache' => false,
                    'debug' => true
                ];

                return new Twig($view, $di, $options, $view->getNamespaces());
            }
        ]);

        $view->assets = new Manager();

        return $view;
    }

    /**
     * Set a test site path
     */
    public function getSitePath()
    {
        return dirname(__DIR__) . '/TestSitePath/';
    }
}