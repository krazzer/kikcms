<?php


namespace KikCMS\Services\Finder;


use Exception;
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
    public function createForFile(FinderFile $finderFile): bool
    {
        if ( ! $this->isEnabled()) {
            return true;
        }

        if ($this->userLevel()) {
            $roles = $this->getGreaterRoles();
        } else {
            $roles = $this->getGreaterAndEqualRoles();
        }

        $fileId = $finderFile->getId();
        $userId = $this->userService->getUserId();

        $this->db->begin();

        try {
            foreach ($roles as $role) {
                $this->create($role, $fileId, FinderConfig::RIGHT_WRITE);
            }

            if ($this->publicByDefault()) {
                $this->create(Permission::VISITOR, $fileId, FinderConfig::RIGHT_READ);
            }

            if ($this->userLevel()) {
                $this->create($userId, $fileId, FinderConfig::RIGHT_WRITE);
            }
        } catch (Exception $e) {
            $this->logger->log(Logger::ERROR, $e);
            $this->db->rollback();
            return false;
        }

        return $this->db->commit();
    }

    /**
     * @param $userIdOrRole
     * @param int $fileId
     * @param string $right
     */
    public function create($userIdOrRole, int $fileId, string $right)
    {
        $permission = new FinderPermission();

        if(is_numeric($userIdOrRole)){
            $permission->user_id = $userIdOrRole;
        } else {
            $permission->role = $userIdOrRole;
        }
        $permission->file_id = $fileId;
        $permission->right   = $right;

        $permission->save();
    }

    /**
     * Remove permission records that are editable by current user
     * @param array $fileIds
     * @throws Exception
     */
    public function deleteByFileIds(array $fileIds)
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
        }

        return $allFileIds;
    }

    /**
     * @param FinderPermissionList $permissionList
     * @param array $fileIds
     * @param bool $saveRecursively
     * @return bool
     * @throws Exception
     */
    public function updateByList(FinderPermissionList $permissionList, array $fileIds, bool $saveRecursively = false): bool
    {
        $this->db->begin();

        if ($saveRecursively) {
            $fileIds = $this->getFileIdsWithSubFiles($fileIds);
        }

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
     * @param int|null $fileId
     * @return bool
     */
    public function canEditId(?int $fileId): bool
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
     * @param int|null $fileId
     * @return bool
     */
    public function canReadId(?int $fileId): bool
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
    public function isEnabled(): bool
    {
        return $this->config->media->filePermissionsEnabled;
    }

    /**
     * @return bool
     */
    public function userLevel(): bool
    {
        if($this->config->media->filePermissionsDefaultUser === true){
            return true;
        }

        if ($this->config->media->filePermissionsDefaultUser === false) {
            return false;
        }

        $roles = (array) $this->config->media->filePermissionsDefaultUser;

        return in_array($this->userService->getRole(), $roles);
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

        if (in_array($role, [Permission::DEVELOPER, $role == Permission::ADMIN])) {
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
     * @return array
     */
    public function getGreaterRoles(): array
    {
        return array_slice(Permission::ROLES, 0, array_search($this->userService->getRole(), Permission::ROLES));
    }

    /**
     * @param string $right
     * @param int|null $fileId
     * @return bool
     */
    private function can(string $right, ?int $fileId): bool
    {
        if ( ! $this->isEnabled()) {
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

    /**
     * @return bool
     */
    private function publicByDefault(): bool
    {
        return $this->config->media->filePermissionsDefaultPublic;
    }
}