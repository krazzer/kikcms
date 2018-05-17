<?php


namespace KikCMS\Services\Finder;


use KikCMS\Classes\Finder\FinderFilters;
use KikCMS\Models\FinderFolder;
use KikCMS\Services\UserService;
use Phalcon\Di\Injectable;

/**
 * @property UserService $userService
 * @property FinderPermissionService $finderPermissionService
 */
class FinderService extends Injectable
{
    /**
     * @return FinderFolder|null
     */
    public function getUserFolder(): ?FinderFolder
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

        if($this->finderPermissionService->canReadId($filters->getFolderId())) {
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
        $folder = FinderFolder::getById($this->session->finderFolderId);

        if ($folder && $this->finderPermissionService->canReadId($folder->getId())) {
            $filters->setFolderId($folder->getId());
            return;
        }

        $this->session->remove('finderFolderId');
    }
}