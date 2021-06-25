<?php

namespace Website\TestClasses;


use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Config\MenuConfig;
use KikCMS\ObjectLists\MenuGroupMap;
use KikCMS\Objects\CmsMenuGroup;
use KikCMS\Objects\CmsMenuItem;
use Phalcon\Mvc\Router\Group;

/**
 * @inheritdoc
 */
class WebsiteSettings extends WebsiteSettingsBase
{
    /**
     * @inheritdoc
     */
    public function addFrontendRoutes(Group $frontend)
    {
        $frontend->add('/test/resourcesexceeded', 'TestWebsite::resourcesExceeded');
        $frontend->add('/test/unauthorized', 'TestWebsite::unauthorized');
        $frontend->add('/test/personform', 'TestModule::personForm');
        $frontend->add('/test/datatableform', 'TestModule::testDataTableForm');
    }

    /**
     * @inheritdoc
     */
    public function addBackendRoutes(Group $backend)
    {
        $backend->add('/test/datatable', 'TestModule::testDataTable');
        $backend->add('/test/datatableform', 'TestModule::testDataTableForm');
        $backend->add('/test/personform', 'TestModule::personForm');
        $backend->add('/test/personimages', 'TestModule::personImages');
    }

    /**
     * @inheritdoc
     */
    public function getMenuGroupMap(MenuGroupMap $menuGroupMap): MenuGroupMap
    {
        $testMenuGroup = (new CmsMenuGroup('test', 'Test'))
            ->add(new CmsMenuItem('datatabletest', 'DataTable test', '/cms/test/datatable'))
            ->add(new CmsMenuItem('personform', 'Person form', '/cms/test/personform'))
            ->add(new CmsMenuItem('personimages', 'Person images', '/cms/test/personimages'));

        return $menuGroupMap->addAfter($testMenuGroup, 'test', MenuConfig::MENU_GROUP_CONTENT);
    }
}