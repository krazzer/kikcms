<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Lib\ModuleContainer;
use Codeception\Util\Autoload;

class Unit extends \Codeception\Module
{
    public function __construct(ModuleContainer $moduleContainer, ?array $config = null)
    {
        parent::__construct($moduleContainer, $config);

        // app\Codeception\UserHelper will be loaded from '/path/to/helpers/UserHelper.php'
        Autoload::addNamespace('Helpers', '/tests/Helpers/');
    }
}
