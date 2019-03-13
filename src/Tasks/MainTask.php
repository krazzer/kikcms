<?php

use KikCMS\Models\File;
use KikCMS\Services\DataTable\PageRearrangeService;
use KikCMS\Services\Finder\FileHashService;
use KikCMS\Services\LanguageService;
use Phalcon\Cli\Task;

/**
 * @property FileHashService $fileHashService
 * @property LanguageService $languageService
 * @property PageRearrangeService $pageRearrangeService
 *
 * @property \KikCmsCore\Services\DbService $dbService
 */
class MainTask extends Task
{
    public function mainAction()
    {
        echo "This is the default task and the default action" . PHP_EOL;
    }

    public function updateNestedSetAction()
    {
        $this->pageRearrangeService->updateNestedSet();
    }

    public function updateMissingFileHashesAction()
    {
        $this->fileHashService->updateMissingHashes();
    }

    public function symlinkAction()
    {
        $query = (new \Phalcon\Mvc\Model\Query\Builder)
            ->columns(['page_id', 'value'])
            ->from(\KikCMS\Models\PageLanguageContent::class)
            ->where('value LIKE :like:', ['like' => '%/finder/file/%']);

        $pageContentList = $this->dbService->getObjects($query);

        foreach ($pageContentList as $pageContent){
            preg_match_all("/finder\/file\/([0-9]+)/", $pageContent->value, $matches);

            foreach($matches[1] as $fileId){
                if( ! $file = File::getById($fileId)){
                    continue;
                }

                $source = SITE_PATH . 'storage/media/' . $file->getFileName();
                $target = SITE_PATH . 'public_html/media/files/' . $file->getFileName();

                if( ! file_exists($target)){
                    symlink($source, $target);
                }

                echo 'Created symlink for: ' . $target . PHP_EOL;

                $this->dbService->query("
                    UPDATE cms_page_language_content 
                    SET value = REPLACE(value, '/finder/file/" . $fileId . "\"', '/media/files/" . $file->getFileName() . "\"')");
            }
        }

//
    }
}