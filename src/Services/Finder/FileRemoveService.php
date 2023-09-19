<?php declare(strict_types=1);


namespace KikCMS\Services\Finder;


use KikCMS\Classes\Translator;
use KikCMS\Config\CacheConfig;
use KikCMS\Config\FinderConfig;
use KikCMS\Config\PlaceholderConfig;
use KikCMS\Models\File;
use KikCMS\ObjectLists\PageLanguageMap;
use KikCMS\Services\CacheService;
use KikCMS\Services\Pages\PageContentService;
use KikCmsCore\Services\DbService;
use KikCMS\Classes\Phalcon\Injectable;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Handles the removal of Files
 *
 * @property CacheService $cacheService
 * @property DbService $dbService
 * @property FileService $fileService
 * @property FilePermissionService $filePermissionService
 * @property PageContentService $pageContentService
 * @property Translator $translator
 */
class FileRemoveService extends Injectable
{
    /**
     * Walk through the public media folder to find and remove broken links
     */
    public function cleanUpBrokenSymlinks(): void
    {
        $filesDir = $this->fileService->getMediaStorageDir() . FinderConfig::FILES_DIR . DIRECTORY_SEPARATOR;

        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($filesDir));

        foreach($objects as $filePath => $object){
            if (is_link($filePath) && ! file_exists($filePath)){
                if(unlink($filePath)){
                    echo 'Removed ' . $filePath . PHP_EOL;
                } else {
                    echo 'Failed to remove ' . $filePath . PHP_EOL;
                }
            }
        }
    }

    /**
     * @param int[] $fileIds
     */
    public function deleteFilesByIds(array $fileIds): void
    {
        $files      = File::getByIdList($fileIds);
        $allFileIds = $fileIds;

        // get sub files
        foreach ($files as $file) {
            $allFileIds = $this->fileService->getFileIdsRecursive($file, $allFileIds);
        }

        $files = File::getByIdList($allFileIds);

        if ( ! $this->dbService->delete(File::class, [File::FIELD_ID => $fileIds])) {
            return;
        }

        foreach ($files as $file) {
            if ( ! $file->isFolder()) {
                $this->unlinkFiles($file);
            }
        }
    }

    /**
     * @param File $file
     * @param bool $canBeEdited
     * @param PageLanguageMap $pageLangMap
     * @return string|null
     */
    public function getDeleteErrorMessage(File $file, bool $canBeEdited, PageLanguageMap $pageLangMap): ?string
    {
        if ($file->key) {
            return $this->translator->tl('media.deleteErrorLocked');
        }

        if ( ! $canBeEdited) {
            return $this->translator->tl('media.errorCantEdit');
        }

        if ($pageLangMap->isEmpty()) {
            return null;
        }

        if ($pageLangMap->count() == 1) {
            return $this->translator->tl('media.deleteErrorLinkedPage', [
                'image'    => $file->getName(),
                'pageName' => $pageLangMap->getFirst()->getName()
            ]);
        }

        return $this->translator->tl('media.deleteErrorLinkedPages', [
            'image'     => $file->getName(),
            'pageNames' => implode(', ', $pageLangMap->getNameMap())
        ]);
    }

    /**
     * Check if the file can be deleted. If not, return the corresponding error message
     *
     * @param File $file
     * @return null|string
     */
    public function getDeleteErrorMessageForFile(File $file): ?string
    {
        $canBeEdited = $this->filePermissionService->canEdit($file);
        $pageLangMap = $this->pageContentService->getLinkedPageLanguageMap($file);

        return $this->getDeleteErrorMessage($file, $canBeEdited, $pageLangMap);
    }

    /**
     * @param File $file
     */
    public function removeThumbNails(File $file): void
    {
        $thumbNailDirs = glob($this->fileService->getMediaThumbDir() . '*');

        foreach ($thumbNailDirs as $thumbNailDir) {
            $thumbFile        = $this->fileService->getMediaThumbPath($file, basename($thumbNailDir));
            $privateThumbFile = $this->fileService->getMediaThumbPath($file, basename($thumbNailDir), true);

            if (file_exists($thumbFile)) {
                unlink($thumbFile);
            }

            if (file_exists($privateThumbFile)) {
                unlink($privateThumbFile);
            }
        }
    }

    /**
     * Remove all files and thumb files for the given File
     *
     * @param File $file
     */
    private function unlinkFiles(File $file): void
    {
        $filePath             = $this->fileService->getFilePath($file);
        $mediaFilePath        = $this->fileService->getMediaFilePath($file);
        $privateMediaFilePath = $this->fileService->getMediaFilePath($file, true);

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        if (file_exists($mediaFilePath)) {
            unlink($mediaFilePath);
        }

        if (is_link($privateMediaFilePath)) {
            unlink($privateMediaFilePath);

            // remove the directory if possible
            @rmdir(dirname($privateMediaFilePath));
        }

        $this->cacheService->clear(PlaceholderConfig::FILE_THUMB_URL . CacheConfig::SEPARATOR . $file->getId());
        $this->cacheService->clear(PlaceholderConfig::FILE_URL . CacheConfig::SEPARATOR . $file->getId());

        $this->removeThumbNails($file);
    }
}