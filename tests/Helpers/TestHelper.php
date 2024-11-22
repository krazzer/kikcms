<?php


namespace Helpers;


use KikCMS\Classes\Frontend\Extendables\MediaResizeBase;
use KikCMS\Classes\Frontend\Extendables\TemplateFieldsBase;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Classes\ImageHandler\ImageHandler;
use KikCMS\Classes\ObjectStorage\File;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Phalcon\KeyValue;
use KikCMS\Classes\Phalcon\Storage\Adapter\Stream;
use KikCMS\Classes\Phalcon\Twig;
use KikCMS\Classes\Phalcon\View;
use KikCMS\Classes\Translator;
use KikCMS\Config\TranslatorConfig;
use KikCMS\ObjectLists\CmsPluginList;
use KikCMS\Services\AssetService;
use KikCMS\Services\CacheService;
use KikCMS\Services\Cms\CmsService;
use KikCMS\Services\DataTable\DataTableFilterService;
use KikCMS\Services\DataTable\TableDataService;
use KikCMS\Services\Finder\FilePermissionService;
use KikCMS\Services\Finder\FileService;
use KikCMS\Services\LanguageService;
use KikCMS\Services\ModelService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\PageService;
use KikCMS\Services\Pages\UrlService;
use KikCMS\Services\TranslationService;
use KikCMS\Services\TwigService;
use KikCMS\Services\UserService;
use KikCMS\Services\Util\PaginateListService;
use KikCMS\Services\Util\QueryService;
use KikCMS\Services\Util\StringService;
use KikCMS\Services\WebForm\RelationKeyService;
use KikCMS\Services\WebForm\StorageService;
use KikCmsCore\Services\DbService;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Phalcon\Cache\Adapter\Memory as MemoryCache;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Di\FactoryDefault;
use Phalcon\Filter\Validation;
use Phalcon\Flash\Session;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaData\Memory;
use Phalcon\Mvc\Url;
use Phalcon\Session\Bag;
use Phalcon\Storage\SerializerFactory;
use PHPUnit\Framework\TestCase;

class TestHelper extends TestCase
{
    /** @var DiInterface */
    private $testDbDi;

    public function __construct()
    {
        parent::__construct('name');
    }

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
            ->onlyMethods(['cache'])
            ->getMock();

        $websiteSettingsMock = $this->getMockBuilder(WebsiteSettingsBase::class)
            ->onlyMethods(['getPluginList'])
            ->getMock();

        $validationMock = $this->getMockBuilder(Validation::class)
//            ->onlyMethods(['setDefaultMessages'])
            ->getMock();

        $cacheServiceMock->method('cache')->willReturn([]);
        $websiteSettingsMock->method('getPluginList')->willReturn(new CmsPluginList);

        $translatorMock = new Translator('en', [
            TranslatorConfig::LANGUAGE_NL => dirname(dirname(__DIR__)) . '/resources/translations/nl.php',
            TranslatorConfig::LANGUAGE_EN => dirname(dirname(__DIR__)) . '/resources/translations/en.php',
        ]);

        $translatorMock->cache              = null;
        $translatorMock->cacheService       = $cacheServiceMock;
        $translatorMock->websiteSettings    = $websiteSettingsMock;
        $translatorMock->validation         = $validationMock;
        $translatorMock->translationService = new TranslationService();

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

        $di = new FactoryDefault();

        $dbConfig = [
            'username' => 'root',
            'password' => 'adminkik12',
            'dbname'   => 'test',
            'host'     => 'mysql',
            'charset'  => 'utf8mb4',
        ];

        // use cms default config
        $config = new Ini(dirname(dirname(__DIR__)) . '/config/config.ini');

        $config->application->path = $this->getSitePath();

        $fileStorage = new File();
        $fileStorage->setStorageDir($this->getSitePath() . 'storage/');

        $adapter = new Stream(new SerializerFactory, [
            'storageDir' => $this->getSitePath() . 'storage/keyvalue/'
        ]);

        $keyValue = new KeyValue($adapter);

        $memoryCache = new MemoryCache(new SerializerFactory);
        $session = new MemoryCache(new SerializerFactory);

        $url = new Url();
        $url->setBaseUri('/');

        $log = new Logger('name');
        $log->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

        $di->set('session', $session);
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
        $di->set('urlService', new UrlService);
        $di->set('dataTableFilterService', new DataTableFilterService);
        $di->set('fileService', new FileService('media', 'thumbs'));
        $di->set('cache', $memoryCache);
        $di->set('translator', $this->getTranslator());
        $di->set('db', new Mysql($dbConfig));
        $di->set('config', $config);
        $di->set('keyValue', $keyValue);
        $di->set('fileStorage', $fileStorage);
        $di->set('url', $url);
        $di->set('storageService', new StorageService);
        $di->set('tableDataService', new TableDataService);
        $di->set('paginateListService', new PaginateListService);
        $di->set('queryService', new QueryService);
        $di->set('stringService', new StringService);
        $di->set('view', $this->getView($di));
        $di->set('logger', $log);
        $di->set('flash', new Session);
        $di->set('filePermissionService', new FilePermissionService);
        $di->set('userService', new UserService);
        $di->set('assetService', new AssetService);
        $di->set('templateFields', new TemplateFieldsBase);
        $di->set('cmsService', new CmsService);
        $di->set('translationService', new TranslationService);
        $di->set('pageService', new PageService);
        $di->set('pageLanguageService', new PageLanguageService);

        $di->get('session')->set('role', Permission::ADMIN);

        Di::setDefault($di);

        $di->set('persistent', new Bag(new \Phalcon\Session\Manager(), 'persistent'));
        $di->set('sessionBag', new Bag(new \Phalcon\Session\Manager(), 'session'));

        $permission = new Permission();
        $permission->setDI($di);

        $di->set('acl', $permission->getAcl());
        $di->set('permisson', $permission);

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
     * @param DiInterface $di
     * @return View
     */
    private function getView(DiInterface $di): View
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
            Twig::DEFAULT_EXTENSION => function (View $view) use ($di) {
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