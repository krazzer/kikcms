<?php


namespace KikCMS\Services\Finder;


use KikCMS\Models\FinderFile;
use KikCMS\ObjectLists\FileMap;
use KikCmsCore\Services\DbService;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property DbService $dbService
 * @property FinderFileService $finderFileService
 */
class FinderHashService extends Injectable
{
    /**
     * Update all finder files where the hash is missing
     */
    public function updateMissingHashes()
    {
        $fileMap = $this->getFileMapMissingHash();

        foreach ($fileMap as $finderFile) {
            $this->updateHash($finderFile);
        }
    }

    /**
     * @return FileMap
     */
    public function getFileMapMissingHash(): FileMap
    {
        $query = (new Builder)
            ->from(FinderFile::class)
            ->where(FinderFile::FIELD_IS_FOLDER . ' = 0')
            ->andWhere(FinderFile::FIELD_HASH . ' IS NULL');

        return $this->dbService->getObjectMap($query, FileMap::class);
    }

    /**
     * @param FinderFile $finderFile
     */
    public function updateHash(FinderFile $finderFile)
    {
        $filePath = $this->finderFileService->getFilePath($finderFile);

        $finderFile->hash = md5_file($filePath);
        $finderFile->save();
    }
}