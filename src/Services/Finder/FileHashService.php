<?php declare(strict_types=1);


namespace KikCMS\Services\Finder;


use KikCMS\Models\File;
use KikCMS\ObjectLists\FileMap;
use KikCmsCore\Services\DbService;
use KikCMS\Classes\Phalcon\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property DbService $dbService
 * @property FileService $fileService
 */
class FileHashService extends Injectable
{
    /**
     * Update all files where the hash is missing
     */
    public function updateMissingHashes(): void
    {
        $fileMap = $this->getFileMapMissingHash();

        foreach ($fileMap as $file) {
            $this->updateHash($file);
        }
    }

    /**
     * @return FileMap
     */
    public function getFileMapMissingHash(): FileMap
    {
        $query = (new Builder)
            ->from(File::class)
            ->where(File::FIELD_IS_FOLDER . ' = 0')
            ->andWhere(File::FIELD_HASH . ' IS NULL');

        return $this->dbService->getObjectMap($query, FileMap::class);
    }

    /**
     * @param File $file
     */
    public function updateHash(File $file): void
    {
        $filePath = $this->fileService->getFilePath($file);

        $file->hash = md5_file($filePath);
        $file->save();
    }
}