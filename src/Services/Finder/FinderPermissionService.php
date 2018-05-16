<?php


namespace KikCMS\Services\Finder;


use Exception;
use KikCMS\Classes\Finder\FinderFileService;
use KikCMS\Classes\Permission;
use KikCMS\Config\FinderConfig;
use KikCMS\Models\FinderFile;
use KikCMS\Models\FinderPermission;
use KikCMS\ObjectLists\FinderPermissionList;
use KikCMS\ObjectLists\UserMap;
use KikCMS\Services\UserService;
use KikCmsCore\Services\DbService;
use Monolog\Logger;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;
use Website\Config\Config;

/**
 * @property DbService $dbService
 * @property Logger $logger
 * @property UserService $userService
 * @property FinderFileService $finderFileService
 * @property Config $config
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
        if ( ! $this->filePermissionsAreManaged()) {
            return true;
        }

        $roles = $this->getGreaterAndEqualRoles();

        $this->db->begin();

        try {
            foreach ($roles as $role) {
                $permission = new FinderPermission();

                $permission->role    = $role;
                $permission->file_id = $finderFile->getId();
                $permission->right   = FinderConfig::RIGHT_WRITE;

                $permission->save();
            }

            if($this->config->media->publicReadByDefault){
                $permission = new FinderPermission();

                $permission->role    = Permission::VISITOR;
                $permission->file_id = $finderFile->getId();
                $permission->right   = FinderConfig::RIGHT_READ;

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
     * @param array $fileIds
     * @throws Exception
     */
    private function deleteByFileIds(array $fileIds)
    {
        $roles   = $this->getEditableRoles();
        $userIds = $this->getEditableUserIds();

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
     * @param array $fileIds
     * @return bool
     */
    public function updateByList(FinderPermissionList $permissionList, array $fileIds): bool
    {
        $this->db->begin();

        $this->deleteByFileIds($fileIds);

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
     * @param int $fileId
     * @return bool
     */
    public function canEditId(int $fileId): bool
    {
        return $this->can(FinderConfig::RIGHT_WRITE, $fileId);
    }

    /**
     * @param FinderFile $file
     * @return bool
     */
    public function canEdit(FinderFile $file): bool
    {
        return $this->canEditId($file->getId());
    }

    /**
     * @param int $fileId
     * @return bool
     */
    public function canReadId(int $fileId): bool
    {
        return $this->can(FinderConfig::RIGHT_READ, $fileId);
    }

    /**
     * @param FinderFile $file
     * @return bool
     */
    public function canRead(FinderFile $file): bool
    {
        return $this->canReadId($file->getId());
    }

    /**
     * @return bool
     */
    public function filePermissionsAreManaged(): bool
    {
        return $this->config->media->manageFilePermissions;
    }

    /**
     * @return array
     */
    public function getEditableKeys(): array
    {
        $roles   = $this->getEditableRoles();
        $userIds = $this->getEditableUserIds();

        return array_merge($roles, $userIds);
    }

    /**
     * @return array
     */
    public function getEditableRoles(): array
    {
        $role  = $this->userService->getRole();
        $roles = Permission::ROLES;

        if (in_array($role, [Permission::DEVELOPER, $role == Permission::ADMIN])){
            $roles = array_slice($roles, array_search($role, $roles));
        } else {
            $roles = array_slice($roles, array_search($role, $roles) + 1);
        }

        return $roles;
    }

    /**
     * @return int[]
     */
    public function getEditableUserIds(): array
    {
        return $this->getEditableUserMap()->keys();
    }

    /**
     * @return UserMap
     */
    public function getEditableUserMap(): UserMap
    {
        $roles = $this->getEditableRoles();

        return $this->userService->getByRoles($roles);
    }

    /**
     * @return array
     */
    public function getGreaterAndEqualRoles(): array
    {
        return array_slice(Permission::ROLES, 0, array_search($this->userService->getRole(), Permission::ROLES) + 1);
    }

    /**
     * @param string $right
     * @param int $fileId
     * @return bool
     */
    private function can(string $right, int $fileId): bool
    {
        if ( ! $this->filePermissionsAreManaged()) {
            return true;
        }

        $userId = $this->userService->getUserId();
        $role   = $this->userService->getRole();

        $query = (new Builder)
            ->from(FinderPermission::class)
            ->where(FinderPermission::FIELD_FILE_ID . ' = :id:', ['id' => $fileId])
            ->andWhere('[' . FinderPermission::FIELD_RIGHT . '] >= :right:', ['right' => $right])
            ->andWhere('user_id = :userId: OR role = :role:', ['userId' => $userId, 'role' => $role]);

        return $this->dbService->getExists($query);
    }
}