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

            if($redirect->path_from === $urlPath){
                $redirect->delete();
            } else {
                $redirect->save();
            }
        }

        $this->writeRedirects();
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

    /**
     * @return void
     */
    public function writeRedirects(): void
    {
        $redirects = Redirect::find();
        $lines     = [];

        foreach ($redirects as $redirect) {
            $lines[] = '    RewriteRule ^' . substr($redirect->path_from, 1) . '/?(.*) ' . $redirect->path_to .
                '/$1 [R=301,L]';
        }

        $lines = implode("\n", $lines);

        $htaccessFile = $this->config->application->path . $this->config->application->publicFolder . '/.htaccess';

        $content = file_get_contents($htaccessFile);

        $sectionStartStr = '#cms-generated-rules-start';
        $sectionEndStr   = '#cms-generated-rules-end';

        $start = strpos($content, $sectionStartStr);

        $newSection = $sectionStartStr . "\n" . $lines . "\n    " . $sectionEndStr;

        if ($start === false) {
            $newSection = "RewriteEngine On\n\n    " . $newSection;
            $newContent = str_replace('RewriteEngine On', $newSection, $content);
        } else {
            $rest = substr($content, $start);
            $end  = strpos($rest, $sectionEndStr);

            $section    = substr($content, $start, $end + strlen($sectionEndStr));
            $newContent = str_replace($section, $newSection, $content);
        }

        file_put_contents($htaccessFile, $newContent);
    }
}
