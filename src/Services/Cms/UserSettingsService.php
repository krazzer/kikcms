<?php declare(strict_types=1);


namespace KikCMS\Services\Cms;


use KikCMS\Objects\UserSettings;
use KikCMS\Services\UserService;
use KikCMS\Classes\Phalcon\Injectable;

/**
 * @property UserService $userService
 */
class UserSettingsService extends Injectable
{
    /**
     * @param string $class
     * @param array $pageIds
     */
    public function storeClosedPageIds(string $class, array $pageIds): void
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
        if( ! $user = $this->userService->getUser()){
            return new UserSettings();
        }

        if ($settings = $user->getSettings()) {
            return (new UserSettings)->unserialize($settings);
        }

        return new UserSettings();
    }

    /**
     * @param UserSettings $settings
     */
    private function store(UserSettings $settings): void
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