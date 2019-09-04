<?php declare(strict_types=1);

namespace KikCMS\Services;


use KikCMS\Classes\CmsPlugin;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Group;

/**
 * @property WebsiteSettingsBase $websiteSettings
 */
class Routing extends Injectable
{
    const MODULE_BACKEND  = 'backend';
    const MODULE_FRONTEND = 'frontend';

    public function initialize()
    {
        $router = new Router(false);

        $backend  = new Group(["module" => self::MODULE_BACKEND]);
        $frontend = new Group(["module" => self::MODULE_FRONTEND]);

        $websiteBackend  = new Group(["module" => "websiteBackend"]);
        $websiteFrontend = new Group(["module" => "websiteFrontend"]);

        $backend->setPrefix('/cms');
        $websiteBackend->setPrefix('/cms');

        $backend->add("", [
            "controller" => "cms",
            "action"     => "index"
        ]);

        $backend->add('/:action', [
            "controller" => "cms",
            "action"     => 1
        ]);

        $backend->add('/stats/index', "Cms::stats");
        $backend->add('/stats/update', "Statistics::update");
        $backend->add('/stats/getVisitors', "Statistics::getVisitors");
        $backend->add('/generate-security-token', "Cms::generateSecurityToken");

        $backend->add('/cache', "Cache::manager")->setName('cacheManager');
        $backend->add('/cache/empty', "Cache::emptyByKey");

        $backend->add("/preview/{pageLanguageId:[0-9]+}", "Cms::preview")->setName('preview');
        $backend->add("/getTinyMceLinks/{languageCode:[a-z]+}", "Cms::getTinyMceLinks");
        $backend->add("/get-urls/{langCode:[a-z]+}", "Cms::getUrls");

        $backend->add("/user-settings/update-closed-page-ids", "UserSettings::updateClosedPageIds");

        $backend->add("/login", [
            "controller" => "login",
            "action"     => "index"
        ]);

        $backend->add("/login/:action", [
            "controller" => "login",
            "action"     => 1
        ]);

        $backend->add("/login/reset-password/{userId:[0-9]+}/{hash:.*}/{time:[0-9]+}", "Login::resetPassword");

        $backend->add("/datatable/pages/:action", [
            "controller" => "pages-data-table",
            "action"     => 1
        ]);

        $backend->add("/datatable/pages/tree-order", "PagesDataTable::treeOrder");

        $backend->add("/datatable/:action", [
            "controller" => "data-table",
            "action"     => 1
        ]);

        $backend->add("/webform/:action", [
            "controller" => "web-form",
            "action"     => 1
        ]);

        $backend->add("/finder/permission/:action", [
            "controller" => "finder-permission",
            "action"     => 1
        ]);

        $backend->add("/finder/:action", [
            "controller" => "finder",
            "action"     => 1
        ]);

        $backend->add('/webform/filepreview/{fileId:[0-9]+}', "Webform::getFilePreview")->setName('webformFilePreview');

        $backend->add('/file/url/{fileId:[0-9]+}', "Finder::url")->setName('fileUrl');
        $backend->add("/file/{fileId:[0-9]+}", "Finder::file")->setName('file');
        $backend->add("/file/key/{key:[0-9a-z\-]+}", "Finder::key");

        $frontend->add('/{url:[0-9a-z\/\-]+}', "Frontend::page")->setName('page');
        $frontend->add('/', "Frontend::page")->setName('page');
        $frontend->add('/page/{lang:[a-z]{2}}/{id:[0-9a-z\-]+}', "Frontend::pageByKey");
        $frontend->add('/page/{lang:[a-z]{2}}/{id:[0-9]+}', "Frontend::pageById");
        $frontend->add('/sitemap.xml', "Robots::sitemap");
        $frontend->add('/robots.txt', "Robots::robots");
        $frontend->add('/object-not-found', "Frontend::objectNotFound")->setName('objectNotFound');

        $frontend->add("/cache/clear/{token:[a-zA-Z0-9\.]+}", "CacheClear::clear")->setName('cacheClear');

        $router->mount($frontend);
        $router->mount($backend);

        $this->addPluginRoutes($router);

        $this->websiteSettings->addBackendRoutes($websiteBackend);
        $this->websiteSettings->addFrontendRoutes($websiteFrontend);

        if ($websiteBackend->getRoutes()) {
            $router->mount($websiteBackend);
        }

        if ($websiteFrontend->getRoutes()) {
            $router->mount($websiteFrontend);
        }

        $router->notFound([
            "module"     => "frontend",
            "controller" => "frontend",
            "action"     => "pageNotFound",
        ]);

        $router->removeExtraSlashes(true);

        return $router;
    }

    /**
     * @param Router $router
     */
    private function addPluginRoutes(Router $router)
    {
        $plugins = $this->websiteSettings->getPluginList();

        /** @var CmsPlugin $plugin */
        foreach ($plugins as $plugin) {
            $pluginBackend  = $this->createPluginGroup(self::MODULE_BACKEND, $plugin);
            $pluginFrontend = $this->createPluginGroup(self::MODULE_FRONTEND, $plugin);

            $plugin->addBackendRoutes($pluginBackend);
            $plugin->addFrontendRoutes($pluginFrontend);

            if ($pluginBackend->getRoutes()) {
                $router->mount($pluginBackend);
            }

            if ($pluginFrontend->getRoutes()) {
                $router->mount($pluginFrontend);
            }
        }
    }

    /**
     * @param string $module
     * @param CmsPlugin $plugin
     * @return Group
     */
    private function createPluginGroup(string $module, CmsPlugin $plugin): Group
    {
        return new Group([
            "module"    => $module,
            'namespace' => $plugin->getControllersNamespace(),
        ]);
    }
}