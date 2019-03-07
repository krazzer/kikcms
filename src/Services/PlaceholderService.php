<?php


namespace KikCMS\Services;


use KikCMS\Config\PlaceholderConfig;
use KikCMS\ObjectLists\PlaceholderMap;
use KikCMS\ObjectLists\PlaceholderTable;
use KikCMS\Objects\Placeholder;
use KikCMS\Services\Finder\FinderFileService;
use Phalcon\Di\Injectable;

/**
 * @property FinderFileService $finderFileService
 */
class PlaceholderService extends Injectable
{
    /**
     * @param string $key
     * @param mixed ...$args
     * @return string
     */
    public function create(string $key, ...$args): string
    {
        return '[[[' . $key . ';' . implode(';', $args) . ']]]';
    }

    /**
     * Get all placeholder data in one array
     *
     * @param string $content
     * @return PlaceholderTable
     */
    public function getTable(string $content): PlaceholderTable
    {
        $placeholderTable = new PlaceholderTable();

        if ( ! preg_match_all('/\[\[\[([a-zA-Z0-9;]+)\]\]\]/', $content, $output)) {
            return $placeholderTable;
        }

        foreach ($output[1] as $index => $item) {
            $args = explode(';', $item);
            $key  = array_shift($args);
            $id   = array_shift($args);

            if ( ! $placeholderTable->has($key)) {
                $placeholderTable->add(new PlaceholderMap, $key);
            }

            $placeholderTable->get($key)->add(new Placeholder($id, $output[0][$index], $args), $id);
        }

        return $placeholderTable;
    }

    /**
     * @param string $content
     * @return string
     */
    public function replaceAll(string $content): string
    {
        $placeholderTable = $this->getTable($content);

        if ($thumbPlaceholderMap = $placeholderTable->get(PlaceholderConfig::FILE_THUMB)) {
            $content = $this->replaceFileThumbs($content, $thumbPlaceholderMap);
        }

        return $content;
    }

    /**
     * @param string $content
     * @param PlaceholderMap $placeholderMap
     * @return string
     */
    private function replaceFileThumbs(string $content, PlaceholderMap $placeholderMap): string
    {
        //17 ms
        $fileMap = $this->finderFileService->getByIdList($placeholderMap->keys());

        //150ms
        foreach ($placeholderMap as $fileId => $placeholder) {
            $thumbUrl = $this->finderFileService->getThumbUrl($fileMap->get($fileId), $placeholder->getArguments()[0]);
            $content  = str_replace($placeholder->getPlaceholder(), $thumbUrl, $content);
        }

        return $content;
    }
}