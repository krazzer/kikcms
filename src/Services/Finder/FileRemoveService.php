<?php


namespace KikCMS\Services\Finder;


use KikCMS\Classes\Translator;
use KikCMS\Models\File;
use KikCMS\Services\Pages\PageContentService;
use KikCmsCore\Services\DbService;
use Phalcon\Di\Injectable;

/**
 * Handles the removal of Files
 *
 * @property DbService $dbService
 * @property FileService $fileService
 * @property FilePermissionService $filePermissionService
 * @property PageContentService $pageContentService
 * @property Translator $translator
 */
class FileRemoveService extends Injectable
{
    /**
     * @param int[] $fileIds
     */
    public function deleteFilesByIds(array $fileIds)
    {
        $files = File::getByIdList($fileIds);
        $allFileIds  = $fileIds;

        // get sub files
        foreach ($files as $file) {
            $allFileIds = $this->fileService->getFileIdsRecursive($file, $allFileIds);
        }

        $files = File::getByIdList($allFileIds);

        if( ! $filesRemoved = $this->dbService->delete(File::class, [File::FIELD_ID => $fileIds])){
            return;
        }

        foreach ($files as $file) {
            if ( ! $file->isFolder()) {
                $this->unlinkFiles($file);
            }
        }
    }

    /**
     * Check if the file can be deleted. If not, return the corresponding error message
     *
     * @param File $file
     * @return null|string
     */
    public function getDeleteErrorMessage(File $file): ?string
    {
        if ($file->key) {
            return $this->translator->tl('media.deleteErrorLocked');
        }

        if ( ! $this->filePermissionService->canEdit($file)) {
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

        foreach ($pageLangMap as $pageLang) {
            $pageNameMap[] = $pageLang->getName();
        }

        return $this->translator->tl('media.deleteErrorLinkedPages', [
            'image'     => $file->getName(),
            'pageNames' => implode(', ', $pageNameMap)
        ]);
    }

    /**
     * @param File $file
     */
    public function removeThumbNails(File $file)
    {
        $thumbNailDirs = glob($this->fileService->getStorageDir() . $this->fileService->getThumbDir() . '/*');

        foreach ($thumbNailDirs as $thumbNailDir) {
            $thumbFile = $this->fileService->getThumbPath($file, basename($thumbNailDir));

            if (file_exists($thumbFile)) {
                unlink($thumbFile);
            }
        }
    }

    /**
     * Remove all files and thumb files for the given File
     *
     * @param File $file
     */
    private function unlinkFiles(File $file)
    {
        unlink($this->fileService->getFilePath($file));
        $this->removeThumbNails($file);
    }
}