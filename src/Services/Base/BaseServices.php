<?php

namespace KikCMS\Services\Base;

use /** @noinspection PhpUndefinedClassInspection */
    ApplicationServices;
use KikCMS\Classes\CmsPlugin;
use KikCMS\Config\KikCMSConfig;
use KikCMS\ObjectLists\CmsPluginList;
use KikCMS\Services\Routing;
use Phalcon\Config;
use Phalcon\Di\FactoryDefault\Cli;
use Phalcon\Loader;
use Phalcon\Mvc\Model\MetaData\Files;
use Website\Classes\WebsiteSettings;

/** @noinspection PhpUndefinedClassInspection */
class BaseServices extends ApplicationServices
{
    /**
     * Contains a list of services that simply return a new instance of themselves
     *
     * @return array
     */
    protected function getSimpleServices(): array
    {
        return [];
    }

    /**
     * Contains a list of services that can be overridden by the Website's variant
     *
     * @return array
     */
    protected function getExtendableServices(): array
    {
        return [];
    }

    /**
     * @param Config $config
     * @param Loader $loader
     */
    public function __construct(Config $config, Loader $loader)
    {
        /** @noinspection PhpUndefinedClassInspection */
        parent::__construct();

        $this->setShared('config', $config);
        $this->setShared('applicationConfig', $config->get('application'));
        $this->setShared('databaseConfig', $config->get('database'));
        $this->setShared('loader', $loader);

        $this->bindServices();
    }

    protected function bindServices()
    {
        $reflection = new \ReflectionObject($this);
        $methods    = $reflection->getMethods();

        foreach ($methods as $method) {
            if ((strlen($method->name) > 10) && (strpos($method->name, 'initShared') === 0)) {
                $this->set(lcfirst(substr($method->name, 10)), $method->getClosure($this));
                continue;
            }

            if ((strlen($method->name) > 4) && (strpos($method->name, 'init') === 0)) {
                $this->set(lcfirst(substr($method->name, 4)), $method->getClosure($this));
            }
        }

        $this->bindExtendableServices();
        $this->bindSimpleServices();
        $this->bindPluginServices();

        // initialize the router if we're not in the Cli
        if ( ! $this instanceof Cli) {
            $this->set('router', function () {
                $routing = new Routing();
                return $routing->initialize();
            });
        }

        if ($this->getApplicationConfig()->env !== KikCMSConfig::ENV_DEV) {
            $this->set('modelsMetadata', function () {
                return new Files([
                    "lifetime"    => 86400,
                    "metaDataDir" => SITE_PATH . "cache/metadata/"
                ]);
            });
        }

        /** @var WebsiteSettings $websiteSettings */
        $websiteServices = $this->get('websiteSettings');
        $overloadedServices = $websiteServices->getOverloadedServices();

        foreach ($overloadedServices as $name => $callable){
            $this->set($name, $callable);
        }
    }

    /**
     * @return Config
     */
    protected function getDatabaseConfig()
    {
        return $this->get('config')->get('database');
    }

    /**
     * @return Config
     */
    protected function getApplicationConfig()
    {
        return $this->get('config')->get('application');
    }

    /**
     * Binds services that are extendable by the website
     */
    private function bindExtendableServices()
    {
        foreach ($this->getExtendableServices() as $service) {
            $serviceName        = lcfirst(last(explode('\\', $service)));
            $serviceWebsiteName = substr($serviceName, 0, -4);
            $classNameWebsite   = 'Website\\Classes\\' . ucfirst($serviceWebsiteName);

            $this->set($serviceWebsiteName, function () use ($service, $classNameWebsite) {
                if (class_exists($classNameWebsite)) {
                    return new $classNameWebsite();
                } else {
                    return new $service();
                }
            });
        }
    }

    /**
     * Bind services required by a plugin
     */
    private function bindPluginServices()
    {
        /** @var CmsPluginList $pluginsList */
        $pluginsList = $this->get('websiteSettings')->getPluginList();

        foreach ($pluginsList as $plugin) {
            $plugin->addServices();
            $this->addPluginSimpleServices($plugin);
        }
    }

    /**
     * Bind simple services that only require a new instance
     */
    private function bindSimpleServices()
    {
        foreach ($this->getSimpleServices() as $service) {
            $serviceName = lcfirst(last(explode('\\', $service)));

            $this->set($serviceName, function () use ($service) {
                return new $service();
            });
        }
    }

    /**
     * @param CmsPlugin $plugin
     */
    private function addPluginSimpleServices(CmsPlugin $plugin)
    {
        $services = $plugin->getSimpleServices();

        foreach ($services as $service) {
            $serviceName = $plugin->getName() . last(explode('\\', $service));

            $this->set($serviceName, function () use ($service) {
                return new $service();
            });
        }
    }
}
