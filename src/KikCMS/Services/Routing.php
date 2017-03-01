<?php

namespace KikCMS\Services;


use Phalcon\Mvc\Router;

class Routing
{
    public function initialize()
    {
        $router = new Router(false);

        $router->setDefaultModule("kikcms");

        $router->add("/deploy", [
            "controller" => "deploy",
            "action"     => "index"
        ]);

        $router->add("/cms", [
            "controller" => "cms",
            "action"     => "index"
        ]);

        $router->add("/cms/:action", [
            "controller" => "cms",
            "action"     => 1
        ]);

        /** Login */
        $router->add("/cms/login", [
            "controller" => "login",
            "action"     => "index"
        ]);

        $router->add("/cms/login/:action", [
            "controller" => "login",
            "action"     => 1
        ]);

        $router->add("/cms/login/reset-password", [
            "controller" => "login",
            "action"     => "resetPassword"
        ]);

        /** DataTable / WebForm */
        $router->add("/cms/datatable/:action", [
            "controller" => "data-table",
            "action"     => 1
        ]);

        $router->add("/cms/webform/:action", [
            "controller" => "web-form",
            "action"     => 1
        ]);

        /** Finder */
        $router->add("/finder/:action", [
            "controller" => "finder",
            "action"     => 1
        ]);

        $router->add("/finder/thumb/{fileId:[0-9]+}", "Finder::thumb")->setName('finderFileThumb');
        $router->add("/finder/file/{fileId:[0-9]+}", "Finder::file")->setName('finderFile');

        /** Not Found */
        $router->notFound([
            "controller" => "errors",
            "action"     => "show404",
        ]);

        $router->removeExtraSlashes(true);

        return $router;
    }
}