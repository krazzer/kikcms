<?php declare(strict_types=1);


namespace KikCMS\Services\Cms;


use KikCMS\Objects\UserSettings;
use KikCMS\Services\UserService;
use Phalcon\Di\Injectable;

/**
 * @property UserService $userService
 */
class UserSettingsService extends Injectable
{
    /**
     * @param string $class
     * @param array $pageIds
     */
    public function storeClosedPageIds(string $class, array $pageIds)
    {
        $settings   = $this->get();
        $pageIdsMap = $settings->getClosedPageIdMap();

        $pageIdsMap[$class] = $pageIds;

        if( ! $pageIds){
            unset($pageIdsMap[$class]);
        }

        $settings->setClosedPageIdMap($pageIdsMap);

        $this->store($settings);
    }

    /**
     * @return UserSettings
     */
    private function get(): UserSettings
    {
        if ($settings = $this->userService->getUser()->getSettings()) {
            return unserialize($settings);
        }

        return new UserSettings();
    }

    /**
     * @param UserSettings $settings
     */
    private function store(UserSettings $settings)
    {
        $user = $this->userService->getUser();

        $user->setSettings($settings->serialize());
        $user->save();
    }

    /**
     * @param string $class
     * @return array
     */
    public function getClosedPageIdsByClass(string $class): array
    {
        $closedPageIdMap = $this->get()->getClosedPageIdMap();

        if(array_key_exists($class, $closedPageIdMap)){
            return $closedPageIdMap[$class];
        }

        return [];
    }
}