<?php


namespace KikCMS\Services\Finder;


use Exception;
use KikCMS\Classes\Finder\FinderFileService;
use KikCMS\Config\FinderConfig;
use KikCMS\Models\FinderFile;
use KikCMS\Models\FinderPermission;
use KikCMS\ObjectLists\FinderPermissionList;
use KikCMS\Services\UserService;
use KikCmsCore\Services\DbService;
use Monolog\Logger;
use Phalcon\Di\Injectable;

/**
 * @property DbService $dbService
 * @property Logger $logger
 * @property UserService $userService
 * @property FinderFileService $finderFileService
 */
class FinderPermissionService extends Injectable
{
    /**
     * Creates default file permissions for given FinderFile
     *
     * @param FinderFile $finderFile
     * @return bool
     */
    public function create(FinderFile $finderFile): bool
    {
        $roles = $this->userService->getGreaterAndEqualRoles();

        $this->db->begin();

        try {
            foreach ($roles as $role) {
                $permission = new FinderPermission();

                $permission->role    = $role;
                $permission->file_id = $finderFile->getId();
                $permission->right   = FinderConfig::RIGHT_WRITE;

                $permission->save();
            }
        } catch (Exception $e) {
            $this->logger->log(Logger::ERROR, $e);
            $this->db->rollback();
            return false;
        }

        return $this->db->commit();
    }

    /**
     * Remove permission records that are editable by current user
     * @param FinderPermissionList $permissionList
     */
    private function deleteByList(FinderPermissionList $permissionList)
    {
        $fileIds = $this->getFileIdsByList($permissionList);

        $roles   = $this->userService->getSubordinateAndEqualRoles();
        $userIds = $this->userService->getSubordinateAndEqualUserIds();

        if ($roles) {
            $this->dbService->delete(FinderPermission::class, [
                FinderPermission::FIELD_ROLE    => $roles,
                FinderPermission::FIELD_FILE_ID => $fileIds,
            ]);
        }

        if ($userIds) {
            $this->dbService->delete(FinderPermission::class, [
                FinderPermission::FIELD_USER_ID => $userIds,
                FinderPermission::FIELD_FILE_ID => $fileIds,
            ]);
        }
    }

    /**
     * Extract all fileIds from given FinderPermissionList
     *
     * @param FinderPermissionList $permissionList
     * @return array
     */
    private function getFileIdsByList(FinderPermissionList $permissionList): array
    {
        $fileIds = [];

        foreach ($permissionList as $finderPermission) {
            if (in_array($finderPermission->file_id, $fileIds)) {
                continue;
            }

            $fileIds[] = $finderPermission->file_id;
        }

        return $fileIds;
    }

    /**
     * @param int[] $fileIds
     * @return int[]
     */
    public function getFileIdsWithSubFiles(array $fileIds): array
    {
        $allFileIds = [];

        $finderFiles = FinderFile::getByIdList($fileIds);

        foreach ($finderFiles as $finderFile) {
            $subFileIds = $this->finderFileService->getFileIdsRecursive($finderFile);
            $allFileIds = array_merge($allFileIds, $subFileIds);

            // add the folder id itself
            if ($finderFile->isFolder()) {
                $allFileIds[] = $finderFile->getId();
            }
        }

        return $allFileIds;
    }

    /**
     * @param FinderPermissionList $permissionList
     * @return bool
     */
    public function updateByList(FinderPermissionList $permissionList): bool
    {
        $this->db->begin();

        $this->deleteByList($permissionList);

        foreach ($permissionList as $finderPermission) {
            try {
                $finderPermission->save();
            } catch (Exception $e) {
                $this->logger->log(Logger::ERROR, $e);
                $this->db->rollback();
                return false;
            }
        }

        return $this->db->commit();
    }
}