<?php
declare(strict_types=1);

namespace Website\Controllers;


use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Classes\Phalcon\Controller;
use KikCmsCore\Exceptions\ResourcesExceededException;

class TestWebsiteController extends Controller
{
    public function resourcesExceededAction()
    {
        throw new ResourcesExceededException();
    }

    public function unauthorizedAction()
    {
        throw new UnauthorizedException();
    }
}