<?php

namespace KikCMS\Services;


use Phalcon\Mvc\Router;

class Routing
{
    public function initialize()
    {
        $router = new Router();

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

        $router->add("/cms/login", [
            "controller" => "login",
            "action"     => "index"
        ]);

        $router->add("/cms/login/:action", [
            "controller" => "login",
            "action"     => 1
        ]);

        $router->removeExtraSlashes(true);

        return $router;
    }
}