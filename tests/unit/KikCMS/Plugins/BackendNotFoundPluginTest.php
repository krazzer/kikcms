<?php
declare(strict_types=1);

use Codeception\Test\Unit;
use KikCMS\Classes\Exceptions\NotFoundException;
use KikCMS\Classes\Exceptions\ObjectNotFoundException;
use KikCMS\Classes\Exceptions\SessionExpiredException;
use KikCMS\Classes\Exceptions\UnauthorizedException;
use KikCMS\Plugins\BackendNotFoundPlugin;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

class BackendNotFoundPluginTest extends Unit
{
    public function testGetActionForException()
    {
        $backendNotFoundPlugin = new BackendNotFoundPlugin();

        $this->assertEquals([null, null, true], $backendNotFoundPlugin->getActionForException(new Exception));
        $this->assertEquals([null, 440, false], $backendNotFoundPlugin->getActionForException(new SessionExpiredException));
        $this->assertEquals(['show404object', null, false], $backendNotFoundPlugin->getActionForException(new ObjectNotFoundException));
        $this->assertEquals(['show404', null, false], $backendNotFoundPlugin->getActionForException(new NotFoundException));
        $this->assertEquals(['show404', null, false], $backendNotFoundPlugin->getActionForException(new DispatcherException('', 2)));
        $this->assertEquals(['show404', null, false], $backendNotFoundPlugin->getActionForException(new DispatcherException('', 5)));
        $this->assertEquals(['show401', 401, false], $backendNotFoundPlugin->getActionForException(new UnauthorizedException));
    }
}
