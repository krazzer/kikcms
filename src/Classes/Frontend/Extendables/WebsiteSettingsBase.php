<?php declare(strict_types=1);

namespace KikCMS\Classes\Frontend\Extendables;


use KikCMS\Classes\Frontend\WebsiteExtendable;
use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Forms\SettingsForm;
use KikCMS\Models\Page;
use KikCMS\ObjectLists\CmsPluginList;
use KikCMS\ObjectLists\MenuGroupMap;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Router\Group;
use Twig\Environment;

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
     * @return null|string
     */
    public function getCustomJs(): ?string
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
     * @return ResponseInterface
     */
    public function getMaintenanceResponse(): ResponseInterface
    {
        $title       = $this->translator->tl('maintenance.title');
        $description = $this->translator->tl('maintenance.description');

        return $this->frontendService->getMessageResponse($title, $description);
    }

    /**
     * @return array
     */
    public function getServices(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getSettingsFormClass(): string
    {
        return SettingsForm::class;
    }

    /**
     * Modify twig envoronment
     * @param Environment $twig
     */
    public function addTwigFunctions(Environment $twig)
    {
    }
}