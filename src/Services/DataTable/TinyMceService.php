<?php declare(strict_types=1);

namespace KikCMS\Services\DataTable;

use KikCMS\Models\Page;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\PageService;
use KikCMS\Services\Pages\UrlService;
use KikCMS\Classes\Phalcon\Injectable;
use Phalcon\Mvc\Url;

/**
 * @property PageLanguageService $pageLanguageService
 * @property PageService $pageService
 * @property UrlService $urlService
 * @property Url $url
 */
class TinyMceService extends Injectable
{
    /**
     * Get an array with page name, url and subitems compatible with tinymce's Link list functionality
     * For performance reasons this is all done with direct array manipulation
     *
     * @param string $languageCode
     * @return array
     */
    public function getLinkList(string $languageCode): array
    {
        $pageUrlData = $this->urlService->getUrlData($languageCode);

        return $this->getLinkListByUrlData($pageUrlData);
    }

    /**
     * @param array $pageUrlData [[id, parent_id, name, slug, type]]
     * @return array
     */
    public function getLinkListByUrlData(array $pageUrlData): array
    {
        $pageUrlDataMap = [];

        foreach ($pageUrlData as $pageUrlDatum) {
            if($pageUrlDatum['type'] == Page::TYPE_MENU) {
                $pageUrlDatum['menu'] = [];
            }

            $pageUrlDataMap[$pageUrlDatum['id']] = $pageUrlDatum;
        }

        $linkList = $this->makeNested($pageUrlDataMap);

        return $this->addUrls($linkList);
    }

    /**
     * @param array $linkList
     * @param string $url
     * @return array
     */
    private function addUrls(array $linkList, string $url = ''): array
    {
        foreach ($linkList as $i => $item) {
            if ($item['type'] !== Page::TYPE_MENU) {
                $subUrl = $url . '/' . $item['slug'];
            } else {
                $subUrl = $url;
            }

            $linkList[$i]['value'] = $subUrl;
            $linkList[$i]['title'] = $item['name'];

            if (isset($item['menu'])) {
                $linkList[$i]['menu'] = $this->addUrls($item['menu'], $subUrl);
            }
        }

        return $linkList;
    }

    /**
     * @param array $source
     * @return array
     */
    private function makeNested(array $source): array
    {
        $nested = array();

        foreach ($source as &$s) {
            if (is_null($s['parent_id'])) {
                $nested[] = &$s;
            } else {
                $pid = $s['parent_id'];
                if (isset($source[$pid])) {
                    if ( ! isset($source[$pid]['menu'])) {
                        $source[$pid]['menu'] = [];
                    }

                    $source[$pid]['menu'][] = &$s;
                }
            }
        }

        return $nested;
    }
}