<?php
declare(strict_types=1);

namespace KikCMS\Classes\Phalcon;


use Phalcon\Mvc\ControllerInterface;

abstract class Controller extends Injectable implements ControllerInterface
{
    /**
     * Phalcon\Mvc\Controller constructor
     */
    public final function __construct() {}
}