<?php

namespace KikCMS\Services\Base;

use /** @noinspection PhpUndefinedClassInspection */
    ApplicationServices;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Services\Routing;
use Phalcon\Config;
use Phalcon\Di\FactoryDefault\Cli;
use Phalcon\Loader;
use Phalcon\Mvc\Model\MetaData\Files;

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

        foreach ($this->getSimpleServices() as $service) {
            $serviceName = lcfirst(last(explode('\\', $service)));

            $this->set($serviceName, function () use ($service) {
                return new $service();
            });
        }

        // initialize the router if we're not in the Cli
        if( ! $this instanceof Cli){
            $this->set('router', function () {
                $routing = new Routing();
                return $routing->initialize();
            });
        }

        if($this->getApplicationConfig()->env == KikCMSConfig::ENV_DEV){
            return;
        }

        $this->set('modelsMetadata', function (){
            return new Files([
                "lifetime"    => 86400,
                "metaDataDir" => SITE_PATH . "/cache/metadata/"
            ]);
        });
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
}
