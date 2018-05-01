<?php


namespace KikCMS\Services\Finder;


use Exception;
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
 */
class FinderPermissionService extends Injectable
{
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

    /**
     * Remove permission records that are editable by current user
     * @param FinderPermissionList $permissionList
     */
    private function deleteByList(FinderPermissionList $permissionList)
    {
        $fileIds = $this->getFileIdsByList($permissionList);

        $roles   = $this->userService->getSubordinateAndEqualRoles();
        $userIds = $this->userService->getSubordinateAndEqualUserIds();

        if($roles){
            $this->dbService->delete(FinderPermission::class, [
                FinderPermission::FIELD_ROLE    => $roles,
                FinderPermission::FIELD_FILE_ID => $fileIds,
            ]);
        }

        if($userIds){
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
}