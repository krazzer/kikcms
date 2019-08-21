<?php declare(strict_types=1);

namespace KikCMS\Classes\Frontend\Extendables;


use KikCMS\Classes\Frontend\WebsiteExtendable;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Models\Page;
use KikCMS\ObjectLists\CmsPluginList;
use KikCMS\ObjectLists\MenuGroupMap;
use Phalcon\Mvc\Router\Group;

/**
 * Contains multiple settings to expand the Cms/Website:
 *
 * - Cms Menu structure
 * - Plugins to load
 * - Backend and Frontend routes
 * - Services load and overload
 */
class WebsiteSettingsBase extends WebsiteExtendable
{
    /**
     * @param MenuGroupMap $menuGroupMap
     * @return MenuGroupMap
     */
    public function getMenuGroupMap(MenuGroupMap $menuGroupMap): MenuGroupMap
    {
        return $menuGroupMap;
    }

    /**
     * @return null|string
     */
    public function getCustomCss(): ?string
    {
        return null;
    }

    /**
     * If present, this route will be used to present a 404 not found page
     *
     * Use Phalcon array route format, for example:
     * "namespace"  => KikCMSConfig::NAMESPACE_PATH_CONTROLLERS,
     * "controller" => "Website",
     * "action"     => "notFound"
     *
     * @return array|null
     */
    public function getNotFoundRoute(): ?array
    {
        return null;
    }

    /**
     * @return string
     */
    public function getPageClass(): string
    {
        return Page::class;
    }

    /**
     * @return array
     */
    public function getPlugins(): array
    {
        return [];
    }

    /**
     * @return CmsPluginList
     */
    public function getPluginList(): CmsPluginList
    {
        $pluginsList = new CmsPluginList();

        $plugins = $this->getPlugins();

        foreach ($plugins as $plugin) {
            $pluginsList->add(new $plugin());
        }

        return $pluginsList;
    }

    /**
     * Limit roles present in the CMS. Empty means all roles are present.
     * @return array
     */
    public function getRoles(): array
    {
        return [];
    }

    /**
     * @param Group $backend
     */
    public function addBackendRoutes(Group $backend)
    {

    }

    /**
     * @param Group $frontend
     */
    public function addFrontendRoutes(Group $frontend)
    {

    }

    /**
     * @param AccessControl $acl
     */
    public function addPermissions(AccessControl $acl)
    {

    }

    /**
     * @return array
     */
    public function getServices(): array
    {
        return [];
    }
}