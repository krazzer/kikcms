<?php
declare(strict_types=1);

namespace KikCMS\Services;


use Phalcon\Cache;
use KikCMS\Classes\Phalcon\IniConfig;
use KikCMS\Classes\Phalcon\Loader;
use KikCMS\Config\CacheConfig;
use KikCMS\Config\KikCMSConfig;
use KikCMS\Classes\Phalcon\Injectable;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @property Cache cache
 * @property IniConfig config
 * @property Loader loader
 */
class NamespaceService extends Injectable
{
    /**
     * @param string $namespace
     * @return array
     */
    public function getClassNamesByNamespace(string $namespace): array
    {
        $cacheKey = 'services.' . str_replace(KikCMSConfig::NAMESPACE_SEPARATOR, '', $namespace);

        if($this->cache && $services = $this->cache->get($cacheKey)){
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
        if($this->cache && $this->config->isProd()){
            $this->cache->set($cacheKey, $services, CacheConfig::ONE_YEAR);
        }

        return $services;
    }

    /**
     * @param string $namespace
     * @return string
     */
    private function getPathByNamespace(string $namespace): string
    {
        $loadedNamespaces = $this->loader->getNamespaces();

        $namespaceParts = explode(KikCMSConfig::NAMESPACE_SEPARATOR, trim($namespace, KikCMSConfig::NAMESPACE_SEPARATOR));

        $path = $loadedNamespaces[$namespaceParts[0]][0];

        array_shift($namespaceParts);

        return $path . implode(DIRECTORY_SEPARATOR, $namespaceParts) . DIRECTORY_SEPARATOR;
    }
}