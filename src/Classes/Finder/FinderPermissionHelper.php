<?php


namespace KikCMS\Classes\Finder;


use KikCMS\Classes\Permission;
use KikCMS\Classes\Translator;
use KikCMS\Config\FinderConfig;
use KikCMS\Models\FinderFile;
use KikCMS\Models\FinderPermission;
use KikCMS\ObjectLists\FinderPermissionList;
use KikCMS\Services\Cms\CmsService;
use KikCMS\Services\Finder\FinderPermissionService;
use KikCMS\Services\UserService;
use KikCmsCore\Services\DbService;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property Translator $translator
 * @property FinderPermissionService $finderPermissionService
 * @property CmsService $cmsService
 * @property DbService $dbService
 * @property UserService $userService
 */
class FinderPermissionHelper extends Injectable
{
    /**
     * @param array $permissionData
     * @param array $fileIds
     * @param bool $saveRecursively
     * @return FinderPermissionList
     */
    public function convertDataToList(array $permissionData, array $fileIds, bool $saveRecursively): FinderPermissionList
    {
        $list = new FinderPermissionList();

        if ($saveRecursively) {
            $fileIds = $this->finderPermissionService->getFileIdsWithSubFiles($fileIds);
        }

        $editableKeys = $this->finderPermissionService->getEditableKeys();

        foreach ($fileIds as $fileId) {
            foreach ($permissionData as $key => $values) {
                // only add values that are allowed to edit
                if ( ! in_array($key, $editableKeys)) {
                    continue;
                }

                $permission = $this->createPermissionByData($fileId, $key, $values);
                $list->add($permission);
            }
        }

        return $list;
    }

    /**
     * Create a title for the permission modal, based on provided fileIds
     *
     * @param int[] $fileIds
     * @return string
     */
    public function getModalTitle(array $fileIds): string
    {
        if (count($fileIds) > 1) {
            return $this->translator->tl('media.button.modal.titleMultiple', ['amount' => count($fileIds)]);
        }

        return FinderFile::getById($fileIds[0])->getName();
    }

    /**
     * Create a permissions table by given file ids, 0 = off, 1 = on, 2 = indeterminate
     * @param array $fileIds
     * @return array [role/userId => [read => 0/1/2 => write => 0/1/2]]
     */
    public function getPermissionTable(array $fileIds): array
    {
        $userIds = $this->finderPermissionService->getEditableUserIds();
        $roles   = $this->finderPermissionService->getEditableRoles();

        $query = (new Builder)
            ->from(FinderPermission::class)
            ->columns(['IFNULL(role, user_id) as key', 'SUM([right] = 1) as read', 'SUM([right] = 2) as write'])
            ->inWhere(FinderPermission::FIELD_FILE_ID, $fileIds)
            ->andWhere('IFNULL(role, user_id) IS NOT NULL')
            ->groupBy('role, user_id');

        if ($userIds) {
            $query->andWhere('user_id IS NULL OR user_id IN({ids:array})', ['ids' => $userIds]);
        } else {
            $query->andWhere('user_id IS NULL');
        }

        $data = $this->dbService->getKeyedRows($query);

        foreach ($data as $key => $datum) {
            if ( ! in_array($key, $userIds) && ! in_array($key, $roles)) {
                $data[$key]['disabled'] = true;
            }

            foreach (['read', 'write'] as $type) {
                if ($datum[$type]) {
                    $data[$key][$type] = $datum[$type] == count($fileIds) ? 1 : 2;
                }
            }
        }

        // disable other roles
        foreach (Permission::ROLES as $role) {
            if ( ! array_key_exists($role, $data)) {
                $data[$role] = ['disabled' => (int) ! in_array($role, $roles)];
            }
        }

        return $data;
    }

    /**
     * @param int $fileId
     * @param int|string $key
     * @param array $values
     * @return FinderPermission
     */
    private function createPermissionByData(int $fileId, $key, array $values): FinderPermission
    {
        $permission = new FinderPermission();

        $permission->right   = $this->getRightByPostValues($values);
        $permission->file_id = $fileId;

        if (is_numeric($key)) {
            $permission->user_id = $key;
        } else {
            $permission->role = $key;
        }

        return $permission;
    }

    /**
     * @param $values
     * @return int
     */
    private function getRightByPostValues($values): int
    {
        $right = FinderConfig::RIGHT_NONE;

        if (isset($values['read'])) {
            $right = FinderConfig::RIGHT_READ;
        }

        if (isset($values['write'])) {
            $right = FinderConfig::RIGHT_WRITE;
        }

        return $right;
    }
}