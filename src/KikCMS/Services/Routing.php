<?php

namespace KikCMS\Services;


use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Group;

class Routing
{
    public function initialize()
    {
        $router = new Router(false);

        $backend  = new Group(["module" => "backend"]);
        $frontend = new Group(["module" => "frontend"]);
        $website  = new Group(["module" => "website"]);

        $backend->setPrefix('/cms');

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

        $backend->add("/preview/{pageLanguageId:[0-9]+}", "Cms::preview")->setName('preview');

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

        $frontend->add("/finder/thumb/{fileId:[0-9]+}", "Finder::thumb")->setName('finderFileThumb');
        $frontend->add("/finder/file/{fileId:[0-9]+}", "Finder::file")->setName('finderFile');

        $frontend->add("/deploy", "Deploy::index");

        $router->mount($frontend);

        if ($website->getRoutes()) {
            $router->mount($website);
        }

        $router->mount($backend);

        $router->notFound([
            "module"     => "frontend",
            "controller" => "frontend",
            "action"     => "pageNotFound",
        ]);

        $router->removeExtraSlashes(true);

        return $router;
    }
}