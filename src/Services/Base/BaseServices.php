<?php /** @noinspection PhpUndefinedClassInspection */

namespace KikCMS\Services\Base;

use ApplicationServices;
use KikCMS\Classes\CmsPlugin;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Config\CacheConfig;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Services\Routing;
use KikCMS\Services\Website\WebsiteService;
use KikCMS\Classes\Phalcon\Loader;
use Phalcon\Cache\BackendInterface;
use Phalcon\Config;
use Phalcon\Di\FactoryDefault\Cli;
use Phalcon\Mvc\Model\MetaData\Files;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

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
        if ( ! $this instanceof Cli) {
            $this->set('router', function () {
                $routing = new Routing();
                return $routing->initialize();
            });
        }

        // initialize models meta data only in production
        if ($this->getAppConfig()->env === KikCMSConfig::ENV_PROD) {
            $this->set('modelsMetadata', function () {
                return new Files([
                    "lifetime"    => 86400,
                    "metaDataDir" => $this->getAppConfig()->path . "cache/metadata/"
                ]);
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
     * @return BackendInterface|null
     */
    protected function getCache(): ?BackendInterface
    {
        return $this->get('cache');
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
     * @return WebsiteService
     */
    protected function getWebsiteService(): WebsiteService
    {
        return $this->get('websiteSettings');
    }

    /**
     * @return Loader
     */
    protected function getLoader(): Loader
    {
        return $this->get('loader');
    }

    /**
     * @param string $namespace
     * @return array
     */
    protected function getClassNamesByNamespace(string $namespace): array
    {
        $cacheKey = 'services:' . $namespace;

        if($this->getCache() && $services = $this->getCache()->get($cacheKey)){
            return $services;
        }

        $services = [];

        $path = $this->getPathByNamespace($namespace);

        if ( ! is_readable($path)) {
            return $services;
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }

            $search  = [$path, '.php', DIRECTORY_SEPARATOR];
            $replace = [null, null, KikCMSConfig::NAMESPACE_SEPARATOR];

            $services[] = $namespace . str_replace($search, $replace, $file->getPathname());
        }

        // only cache on production, to prevent errors when creating new services
        if($this->getCache() && $this->getAppConfig()->env == KikCMSConfig::ENV_PROD){
            $this->getCache()->save($cacheKey, $services, CacheConfig::ONE_YEAR);
        }

        return $services;
    }

    /**
     * @param string $namespace
     * @return string
     */
    protected function getPathByNamespace(string $namespace): string
    {
        $loadedNamespaces = $this->getLoader()->getNamespaces();

        $namespaceParts = explode(KikCMSConfig::NAMESPACE_SEPARATOR, trim($namespace, KikCMSConfig::NAMESPACE_SEPARATOR));

        $path = $loadedNamespaces[$namespaceParts[0]][0];

        array_shift($namespaceParts);

        return $path . implode(DIRECTORY_SEPARATOR, $namespaceParts) . DIRECTORY_SEPARATOR;
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
    }
}
