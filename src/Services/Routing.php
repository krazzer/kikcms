<?php

namespace KikCMS\Services;


use KikCMS\Classes\Frontend\Extendables\WebsiteRoutingBase;
use KikCMS\Services\Website\WebsiteService;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Group;

/**
 * @property WebsiteService $websiteService
 * @property WebsiteRoutingBase $websiteRouting
 */
class Routing extends Injectable
{
    public function initialize()
    {
        $router = new Router(false);

        $backend  = new Group(["module" => "backend"]);
        $frontend = new Group(["module" => "frontend"]);

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

        $backend->add('/stats/index', "Cms::statsIndex");
        $backend->add('/stats/sources', "Cms::statsSources");
        $backend->add('/stats/update', "Statistics::update");

        $backend->add("/preview/{pageLanguageId:[0-9]+}", "Cms::preview")->setName('preview');
        $backend->add("/getTinyMceLinks/{languageCode:[a-z]+}", "Cms::getTinyMceLinks");

        $backend->add("/login", [
            "controller" => "login",
            "action"     => "index"
        ]);

        $backend->add("/login/:action", [
            "controller" => "login",
            "action"     => 1
        ]);

        $backend->add("/login/reset-password", [
            "controller" => "login",
            "action"     => "resetPassword"
        ]);

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

        $backend->add("/finder/:action", [
            "controller" => "finder",
            "action"     => 1
        ]);

        $frontend->add('/{url:[0-9a-z\/\-]+}', "Frontend::page")->setName('page');
        $frontend->add('/', "Frontend::page")->setName('page');
        $frontend->add('/page/{lang:[a-z]{2}}/{id:[0-9a-z\-]+}', "Frontend::pageByKey");
        $frontend->add('/page/{lang:[a-z]{2}}/{id:[0-9]+}', "Frontend::pageById");

        $frontend->add("/finder/file/{fileId:[0-9]+}", "Finder::file")->setName('finderFile');
        $frontend->add("/finder/thumb/{fileId:[0-9]+}", "Finder::thumb")->setName('finderFileThumb');
        $frontend->add('/finder/thumb/{type:[0-9a-z\/\-]+}/{fileId:[0-9]+}', [
            "controller" => "finder",
            "action"     => "thumb",
            "fileId"     => 2,
            "type"       => 1,
        ]);

        $frontend->add("/deploy", "Deploy::index");

        $router->mount($frontend);
        $router->mount($backend);

        $this->websiteRouting->addBackendRoutes($websiteBackend);
        $this->websiteRouting->addFrontendRoutes($websiteFrontend);

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
}