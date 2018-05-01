<?php


namespace KikCMS\Classes\Finder;


use KikCMS\Config\FinderConfig;
use KikCMS\Models\FinderPermission;
use KikCMS\ObjectLists\FinderPermissionList;
use Phalcon\Di\Injectable;

class FinderPermissionPostHelper extends Injectable
{
    /**
     * @param array $permission
     * @param array $fileIds
     * @return FinderPermissionList
     */
    public function convertDataToList(array $permission, array $fileIds): FinderPermissionList
    {
        $list = new FinderPermissionList();

        foreach ($fileIds as $fileId) {
            foreach ($permission as $key => $values) {
                $permission = $this->createPermissionByData($fileId, $key, $values);
                $list->add($permission);
            }
        }

        return $list;
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