<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module;

class Acceptance extends Module
{
    public function resetSession()
    {
        $this->getModule('WebDriver')->_closeSession();
        $this->getModule('WebDriver')->_initializeSession();
    }
}
