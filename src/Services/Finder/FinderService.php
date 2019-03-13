<?php


namespace KikCMS\Services\Finder;


use KikCMS\Classes\Finder\FinderFilters;
use KikCMS\Models\Folder;
use KikCMS\Services\UserService;
use Phalcon\Di\Injectable;

/**
 * @property UserService $userService
 * @property FilePermissionService $filePermissionService
 */
class FinderService extends Injectable
{
    /**
     * @return Folder|null
     */
    public function getUserFolder(): ?Folder
    {
        return $this->userService->getUser()->folder;
    }

    /**
     * Determine what folder the user should start in, and set it via the filters
     * @param FinderFilters $filters
     */
    public function setStartingFolder(FinderFilters $filters)
    {
        if ($this->session->finderFolderId) {
            $this->setFolderBySession($filters);
            return;
        }

        if($this->filePermissionService->canReadId($filters->getFolderId())) {
            return;
        }

        if( ! $userFolder = $this->getUserFolder()){
            return;
        }

        $filters->setFolderId($userFolder->getId());
    }

    /**
     * Sets the starting folder to a previously visited one using session
     * @param FinderFilters $filters
     */
    public function setFolderBySession(FinderFilters $filters)
    {
        $folder = Folder::getById($this->session->finderFolderId);

        if ($folder && $this->filePermissionService->canReadId($folder->getId())) {
            $filters->setFolderId($folder->getId());
            return;
        }

        $this->session->remove('finderFolderId');
    }
}