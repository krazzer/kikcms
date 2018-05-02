<?php


namespace KikCMS\Classes\Finder;


use KikCMS\Classes\Translator;
use KikCMS\Config\FinderConfig;
use KikCMS\Models\FinderFile;
use KikCMS\Models\FinderPermission;
use KikCMS\ObjectLists\FinderPermissionList;
use KikCMS\Services\Cms\CmsService;
use KikCMS\Services\Finder\FinderPermissionService;
use KikCmsCore\Services\DbService;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property Translator $translator
 * @property FinderPermissionService $finderPermissionService
 * @property CmsService $cmsService
 * @property DbService $dbService
 */
class FinderPermissionHelper extends Injectable
{
    /**
     * @param array $permissionData
     * @param array $fileIds
     * @return FinderPermissionList
     */
    public function convertDataToList(array $permissionData, array $fileIds): FinderPermissionList
    {
        $list = new FinderPermissionList();

        foreach ($fileIds as $fileId) {
            foreach ($permissionData as $key => $values) {
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
        $query = (new Builder)
            ->from(FinderPermission::class)
            ->columns(['IFNULL(role, user_id) as key', 'SUM([right] = 1) as read', 'SUM([right] = 2) as write'])
            ->inWhere(FinderPermission::FIELD_FILE_ID, $fileIds)
            ->andWhere('IFNULL(role, user_id) IS NOT NULL')
            ->groupBy('role, user_id');

        $data = $this->dbService->getKeyedRows($query);

        foreach ($data as &$datum) {
            foreach (['read', 'write'] as $type) {
                if ($datum[$type]) {
                    $datum[$type] = $datum[$type] == count($fileIds) ? 1 : 2;
                }
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