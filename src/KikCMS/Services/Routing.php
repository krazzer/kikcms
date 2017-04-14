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

        $backend->add("/deploy", [
            "controller" => "deploy",
            "action"     => "index"
        ]);

        $backend->add("/cms", [
            "controller" => "cms",
            "action"     => "index"
        ]);

        $backend->add('/cms/{action:[0-9a-z\/\-]+}', [
            "controller" => "cms",
            "action"     => 1
        ]);

        $backend->add("/cms/preview/{pageLanguageId:[0-9]+}", "Cms::preview")->setName('preview');

        /** Login */
        $backend->add("/cms/login", [
            "controller" => "login",
            "action"     => "index"
        ]);

        $backend->add("/cms/login/:action", [
            "controller" => "login",
            "action"     => 1
        ]);

        $backend->add("/cms/login/reset-password", [
            "controller" => "login",
            "action"     => "resetPassword"
        ]);

        /** Pages DataTable */
        $backend->add("/cms/datatable/pages/:action", [
            "controller" => "pages-data-table",
            "action"     => 1
        ]);

        $backend->add("/cms/datatable/pages/tree-order", "PagesDataTable::treeOrder");

        /** DataTable / WebForm */
        $backend->add("/cms/datatable/:action", [
            "controller" => "data-table",
            "action"     => 1
        ]);

        $backend->add("/cms/webform/:action", [
            "controller" => "web-form",
            "action"     => 1
        ]);

        /** Finder */
        $backend->add("/finder/:action", [
            "controller" => "finder",
            "action"     => 1
        ]);

        $frontend->add('/{url:[0-9a-z\/\-]+}', "Frontend::page")->setName('page');
        $frontend->add('/', "Frontend::page")->setName('page');

        $frontend->add("/finder/thumb/{fileId:[0-9]+}", "Finder::thumb")->setName('finderFileThumb');
        $frontend->add("/finder/file/{fileId:[0-9]+}", "Finder::file")->setName('finderFile');

        $router->mount($frontend);

        if($website->getRoutes()){
            $router->mount($website);
        }

        $router->mount($backend);

        /** Not Found */
        $router->notFound([
            "module"     => "frontend",
            "controller" => "frontend-errors",
            "action"     => "show404",
        ]);

        $router->removeExtraSlashes(true);

        return $router;
    }
}