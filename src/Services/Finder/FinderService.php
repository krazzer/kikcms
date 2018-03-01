<?php


namespace KikCMS\Services\Finder;


use KikCMS\Classes\Finder\FinderFilters;
use KikCMS\Models\FinderFolder;
use KikCMS\Services\UserService;
use Phalcon\Di\Injectable;

/**
 * @property UserService $userService
 */
class FinderService extends Injectable
{
    /**
     * @return FinderFolder
     */
    public function getUserFolder(): FinderFolder
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

        if($this->userService->allowedInFolderId($filters->getFolderId())) {
            return;
        }

        $userFolderId = $this->getUserFolder()->getId();

        $filters->setFolderId($userFolderId);
    }

    /**
     * Sets the starting folder to a previously visited one using session
     * @param FinderFilters $filters
     */
    public function setFolderBySession(FinderFilters $filters)
    {
        $folder = FinderFolder::getById($this->session->finderFolderId);

        if ($folder && $this->userService->allowedInFolderId($folder->getId())) {
            $filters->setFolderId($folder->getId());
            return;
        }

        $this->session->remove('finderFolderId');
    }
}