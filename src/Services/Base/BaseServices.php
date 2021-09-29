<?php /** @noinspection PhpUndefinedClassInspection */
declare(strict_types=1);

namespace KikCMS\Services\Base;

use ApplicationServices;
use KikCMS\Classes\CmsPlugin;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Classes\Phalcon\IniConfig;
use KikCMS\Services\NamespaceService;
use KikCMS\Services\Routing;
use KikCMS\Classes\Phalcon\Loader;
use Phalcon\Config;
use Phalcon\Di\FactoryDefault\Cli;
use Phalcon\Mvc\Model\MetaData\Stream;
use ReflectionObject;

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
        parent::__construct();

        $this->setShared('config', $config);
        $this->setShared('applicationConfig', $config->get('application'));
        $this->setShared('databaseConfig', $config->get('database'));
        $this->setShared('loader', $loader);
        $this->setShared('namespaceService', function () {
            return new NamespaceService();
        });

        $this->bindServices();
    }

    /**
     * @param string|null $group
     * @param string|null $item
     * @return mixed
     */
    public function getConfig(string $group = null, string $item = null)
    {
        $config = $this->get('config');

        if ( ! $group) {
            return $config;
        }

        if ( ! $item) {
            return $config->get($group);
        }

        return $config->get($group)->get($item);
    }

    /**
     * Binds all services
     */
    protected function bindServices()
    {
        $this->bindMethodServices();
        $this->bindExtendableServices();
        $this->bindSimpleServices();
        $this->bindPluginServices();

        // initialize the router if we're not in the Cli
        /** @noinspection PhpInstanceofIsAlwaysTrueInspection */
        if ( ! $this instanceof Cli) {
            $this->set('router', function () {
                $routing = new Routing();
                return $routing->initialize();
            });
        }

        // initialize models meta data only in production
        if ($this->getIniConfig()->isDev()) {
            $this->set('modelsMetadata', function () {
                $dir = $this->getAppConfig()->path . "cache/metadata/";

                if( ! file_exists($dir)){
                    mkdir($dir);
                }

                return new Stream(["lifetime"    => 86400, "metaDataDir" => $dir]);
            });
        }

        $overloadedServices = $this->getWebsiteSettings()->getServices();

        foreach ($overloadedServices as $name => $callable) {
            if ( ! is_callable($callable)) {
                continue;
            }

            $this->set($name, $callable);
        }
    }

    /**
     * @return IniConfig
     */
    protected function getIniConfig(): IniConfig
    {
        return $this->get('config');
    }

    /**
     * @return Config
     */
    protected function getDbConfig(): Config
    {
        return $this->get('config')->get('database');
    }

    /**
     * @return Config
     */
    protected function getAppConfig(): Config
    {
        return $this->get('config')->get('application');
    }

    /**
     * @return WebsiteSettingsBase
     */
    protected function getWebsiteSettings(): WebsiteSettingsBase
    {
        return $this->get('websiteSettings');
    }

    /**
     * Binds services that are extendable by the website
     */
    private function bindExtendableServices()
    {
        foreach ($this->getExtendableServices() as $service) {
            $serviceName        = lcfirst(last(explode('\\', $service)));
            $serviceWebsiteName = substr($serviceName, 0, -4);
            $namespace          = $this->getIniConfig()->application->extendableClassesNamespace;
            $classNameWebsite   = $namespace . ucfirst($serviceWebsiteName);

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
        $pluginList = $this->getWebsiteSettings()->getPluginList();

        /** @var CmsPlugin $plugin */
        foreach ($pluginList as $plugin) {
            $plugin->addServices($this);
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

            if ($this->has($serviceName)) {
                continue;
            }

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

    /**
     * Bind services by methods of the current class that start with init or initShared
     */
    private function bindMethodServices()
    {
        $reflection = new ReflectionObject($this);
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
    }
}
