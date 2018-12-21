<?php


namespace Helpers;


use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Classes\Translator;
use KikCMS\ObjectLists\CmsPluginList;
use KikCMS\Services\CacheService;
use PHPUnit\Framework\TestCase;

class TestHelper extends TestCase
{
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
}