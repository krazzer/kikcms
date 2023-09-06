<?php declare(strict_types=1);

namespace KikCMS\Objects\Redirect;

use KikCMS\Classes\Phalcon\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

class RedirectService extends Injectable
{
    /**
     * @param string $previousUrlPath
     * @param string $urlPath
     * @param int $pageLanguageId
     * @return void
     */
    public function add(string $previousUrlPath, string $urlPath, int $pageLanguageId)
    {
        $redirectMap = $this->getByPageLanguageId($pageLanguageId);

        $redirect = new Redirect();

        $redirect->path_from        = $previousUrlPath;
        $redirect->path_to          = $urlPath;
        $redirect->page_language_id = $pageLanguageId;

        $redirect->save();

        foreach ($redirectMap as $redirect) {
            $redirect->path_to = $urlPath;
            $redirect->save();
        }
    }

    /**
     * @param int $pageLanguageId
     * @return RedirectMap
     */
    public function getByPageLanguageId(int $pageLanguageId): RedirectMap
    {
        $query = (new Builder)
            ->from(Redirect::class)
            ->inWhere(Redirect::FIELD_PAGE_LANGUAGE_ID, [$pageLanguageId]);

        return $this->dbService->getObjectMap($query, RedirectMap::class);
    }
}
