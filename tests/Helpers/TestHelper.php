<?php


namespace Helpers;


use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Classes\Translator;
use KikCMS\ObjectLists\CmsPluginList;
use KikCMS\Services\CacheService;
use KikCMS\Services\LanguageService;
use KikCmsCore\Services\DbService;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di;
use Phalcon\DiInterface;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaData\Memory;
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
        if( ! defined('SITE_PATH')){
            define('SITE_PATH', null);
        }

        $cacheServiceMock = $this->getMockBuilder(CacheService::class)
            ->setMethods(['cache'])
            ->getMock();

        $websiteSettingsMock = $this->getMockBuilder(WebsiteSettingsBase::class)
            ->setMethods(['getPluginList'])
            ->getMock();

        $cacheServiceMock->method('cache')->willReturn([]);
        $websiteSettingsMock->method('getPluginList')->willReturn(new CmsPluginList);

        $translatorMock = new Translator('nl');

        $translatorMock->cache        = null;
        $translatorMock->cacheService = $cacheServiceMock;
        $translatorMock->websiteSettings = $websiteSettingsMock;

        return $translatorMock;
    }

    /**
     * @return DiInterface
     */
    public function getTestDbDi(): DiInterface
    {
        if($this->testDbDi){
            return $this->testDbDi;
        }

        $di = new Di\FactoryDefault();

        $cacheService = new CacheService();

        $cacheService->cache = false;

        $dbConfig = [
            'username' => 'root',
            'password' => 'adminkik12',
            'dbname'   => 'test',
            'host'     => '127.0.0.1',
            'charset'  => 'utf8mb4',
        ];

        $di->set('languageService', new LanguageService());
        $di->set('modelsManager', new Manager());
        $di->set('modelsMetadata', new Memory());
        $di->set('cacheService', $cacheService);
        $di->set('db', new Mysql($dbConfig));
        $di->set('dbService', new DbService());

        Di::setDefault($di);

        $this->testDbDi = $di;

        return $this->testDbDi;
    }
}