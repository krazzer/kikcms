<?php


namespace KikCMS\Services;


use KikCMS\Classes\Phalcon\Cache;
use KikCMS\Config\CacheConfig;
use KikCMS\Config\PlaceholderConfig;
use KikCMS\ObjectLists\FileMap;
use KikCMS\ObjectLists\PlaceholderFileThumbUrlMap;
use KikCMS\ObjectLists\PlaceholderFileUrlMap;
use KikCMS\ObjectLists\PlaceholderTable;
use KikCMS\Services\Finder\FileService;
use KikCMS\Services\Pages\UrlService;
use Phalcon\Di\Injectable;

/**
 * @property Cache $cache
 * @property CacheService $cacheService
 * @property FileService $fileService
 * @property UrlService $urlService
 */
class PlaceholderService extends Injectable
{
    /**
     * Return the placeholder, or the value if it's cached
     *
     * @param string $type
     * @param mixed ...$args
     * @return string
     */
    public function getValue(string $type, ...$args): string
    {
        $key = $type . CacheConfig::SEPARATOR . implode(CacheConfig::SEPARATOR, $args);

        if ($this->cache && $value = $this->cache->get($key)) {
            return $value;
        }

        return '[[[' . $key . ']]]';
    }

    /**
     * @param string $content
     * @return string
     */
    public function replaceAll(string $content): string
    {
        if ( ! preg_match_all('/\[\[\[([a-zA-Z0-9:-]+)\]\]\]/', $content, $output)) {
            return $content;
        }

        $placeholderTable = new PlaceholderTable();
        $replaceMap       = [];

        foreach ($output[1] as $index => $key) {
            $args = explode(CacheConfig::SEPARATOR, $key);
            $type = array_shift($args);

            $className    = PlaceholderConfig::CLASS_MAP[$type];
            $mapClassName = PlaceholderConfig::MAP_CLASS_MAP[$type];

            if ( ! $placeholderTable->has($type)) {
                $placeholderTable->add(new $mapClassName, $type);
            }

            $placeholder = $output[0][$index];

            $placeholderTable->get($type)->add(new $className($key, $placeholder, $args), $key);
        }


        if ($placeholderMap = $placeholderTable->get(PlaceholderConfig::FILE_THUMB_URL)) {
            $replaceMap = array_merge($replaceMap, $this->getFileThumbUrlReplaceMap($placeholderMap));
        }

        if ($placeholderMap = $placeholderTable->get(PlaceholderConfig::FILE_URL)) {
            $replaceMap = array_merge($replaceMap, $this->getFileUrlReplaceMap($placeholderMap));
        }

        return str_replace(array_keys($replaceMap), array_values($replaceMap), $content);
    }

    /**
     * @param PlaceholderFileThumbUrlMap $placeholderMap
     * @return array [key => value]
     */
    private function getFileThumbUrlReplaceMap(PlaceholderFileThumbUrlMap $placeholderMap): array
    {
        $replaceMap = [];
        $fileMap    = $this->getFileMap($placeholderMap);

        foreach ($placeholderMap as $key => $placeholder) {
            if( ! $file = $fileMap->get($placeholder->getFileId())){
                $replaceMap[$placeholder->getPlaceholder()] = null;
                continue;
            }

            $type    = $placeholder->getType();
            $private = $placeholder->isPrivate();

            $thumbUrl = $this->fileService->getThumbUrl($file, $type, $private);

            $replaceMap[$placeholder->getPlaceholder()] = $thumbUrl;

            $this->cache->save($key, $thumbUrl, CacheConfig::ONE_YEAR);
        }

        return $replaceMap;
    }

    /**
     * @param PlaceholderFileUrlMap $placeholderMap
     * @return array [key => value]
     */
    private function getFileUrlReplaceMap(PlaceholderFileUrlMap $placeholderMap): array
    {
        $replaceMap = [];
        $fileMap    = $this->getFileMap($placeholderMap);

        foreach ($placeholderMap as $key => $placeholder) {
            if( ! $file = $fileMap->get($placeholder->getFileId())){
                $replaceMap[$placeholder->getPlaceholder()] = null;
                continue;
            }

            $url = $this->fileService->getUrl($file, $placeholder->isPrivate());

            $replaceMap[$placeholder->getPlaceholder()] = $url;

            $this->cache->save($key, $url, CacheConfig::ONE_YEAR);
        }

        return $replaceMap;
    }

    /**
     * @param PlaceholderFileUrlMap $placeholderMap
     * @return FileMap
     */
    private function getFileMap(PlaceholderFileUrlMap $placeholderMap): FileMap
    {
        $fileIdList = [];

        foreach ($placeholderMap as $key => $placeholder) {
            $fileIdList[] = $placeholder->getFileId();
        }

        return $this->fileService->getByIdList($fileIdList);
    }
}