<?php


namespace KikCMS\Services\Finder;


use KikCMS\Classes\Translator;
use KikCMS\Models\FinderFile;
use KikCMS\Services\Pages\PageContentService;
use KikCmsCore\Services\DbService;
use Phalcon\Di\Injectable;

/**
 * Handles the removal of FinderFiles
 *
 * @property DbService $dbService
 * @property FinderFileService $finderFileService
 * @property FinderPermissionService $finderPermissionService
 * @property PageContentService $pageContentService
 * @property Translator $translator
 */
class FinderFileRemoveService extends Injectable
{
    /**
     * @param int[] $fileIds
     */
    public function deleteFilesByIds(array $fileIds)
    {
        $finderFiles = FinderFile::getByIdList($fileIds);

        // get sub files
        foreach ($finderFiles as $file) {
            $fileIds = $this->finderFileService->getFileIdsRecursive($file, $fileIds);
        }

        $finderFiles  = FinderFile::getByIdList($fileIds);
        $filesRemoved = $this->dbService->delete(FinderFile::class, [FinderFile::FIELD_ID => $fileIds]);

        if ($filesRemoved) {
            foreach ($finderFiles as $finderFile) {
                if ( ! $finderFile->isFolder()) {
                    $this->unlinkFiles($finderFile);
                }
            }
        }
    }

    /**
     * Check if the file can be deleted. If not, return the corresponding error message
     *
     * @param FinderFile $file
     * @return null|string
     */
    public function getDeleteErrorMessage(FinderFile $file): ?string
    {
        if ($file->key) {
            return $this->translator->tl('media.deleteErrorLocked');
        }

        if ( ! $this->finderPermissionService->canEdit($file)) {
            return $this->translator->tl('media.errorCantEdit');
        }

        if (($pageLangMap = $this->pageContentService->fileIsLinked($file))->isEmpty()) {
            return null;
        }

        if ($pageLangMap->count() == 1) {
            return $this->translator->tl('media.deleteErrorLinkedPage', [
                'image'    => $file->getName(),
                'pageName' => $pageLangMap->getFirst()->getName()
            ]);
        }

        $pageNameMap = [];

        foreach ($pageLangMap as $pageLang){
            $pageNameMap[] = $pageLang->getName();
        }

        return $this->translator->tl('media.deleteErrorLinkedPages', [
            'image'     => $file->getName(),
            'pageNames' => implode(', ', $pageNameMap)
        ]);
    }

    /**
     * @param FinderFile $finderFile
     */
    public function removeThumbNails(FinderFile $finderFile)
    {
        $thumbNailDirs = glob($this->finderFileService->getStorageDir() . $this->finderFileService->getThumbDir() . '/*');

        foreach ($thumbNailDirs as $thumbNailDir) {
            $thumbFile = $this->finderFileService->getThumbPath($finderFile, basename($thumbNailDir));

            if (file_exists($thumbFile)) {
                unlink($thumbFile);
            }
        }
    }

    /**
     * Remove all files and thumb files for the given FinderFile
     *
     * @param FinderFile $finderFile
     */
    private function unlinkFiles(FinderFile $finderFile)
    {
        unlink($this->finderFileService->getFilePath($finderFile));
        $this->removeThumbNails($finderFile);
    }
}