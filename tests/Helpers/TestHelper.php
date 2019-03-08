<?php


namespace Helpers;


use KikCMS\Classes\Frontend\Extendables\MediaResizeBase;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Classes\ImageHandler\ImageHandler;
use KikCMS\Classes\ObjectStorage\File;
use KikCMS\Classes\Translator;
use KikCMS\ObjectLists\CmsPluginList;
use KikCMS\Services\CacheService;
use KikCMS\Services\Finder\FinderFileService;
use KikCMS\Services\LanguageService;
use KikCmsCore\Services\DbService;
use Phalcon\Cache\Frontend\Data;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di;
use Phalcon\DiInterface;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaData\Memory;
use Phalcon\Mvc\Url;
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
        $this->setSitePath();

        $cacheServiceMock = $this->getMockBuilder(CacheService::class)
            ->setMethods(['cache'])
            ->getMock();

        $websiteSettingsMock = $this->getMockBuilder(WebsiteSettingsBase::class)
            ->setMethods(['getPluginList'])
            ->getMock();

        $cacheServiceMock->method('cache')->willReturn([]);
        $websiteSettingsMock->method('getPluginList')->willReturn(new CmsPluginList);

        $translatorMock = new Translator('nl');

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

        $this->setSitePath();

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

        $fileStorage = new File();
        $fileStorage->setStorageDir(SITE_PATH . 'storage/');

        $url = new Url();
        $url->setBaseUri('/');

        $di->set('languageService', new LanguageService);
        $di->set('modelsManager', new Manager);
        $di->set('modelsMetadata', new Memory);
        $di->set('imageHandler', new ImageHandler);
        $di->set('dbService', new DbService);
        $di->set('mediaResize', new MediaResizeBase);
        $di->set('cacheService', new CacheService);
        $di->set('finderFileService', new FinderFileService('media', 'thumbs'));
        $di->set('cache', new \Phalcon\Cache\Backend\Memory(new Data));
        $di->set('db', new Mysql($dbConfig));
        $di->set('config', $config);
        $di->set('fileStorage', $fileStorage);
        $di->set('url', $url);

        Di::setDefault($di);

        $this->testDbDi = $di;

        return $this->testDbDi;
    }

    /**
     * Set a test site path
     */
    private function setSitePath()
    {
        if ( ! defined('SITE_PATH')) {
            define('SITE_PATH', dirname(__DIR__) . '/TestSitePath/');
        }
    }
}