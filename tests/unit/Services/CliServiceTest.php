<?php
declare(strict_types=1);

namespace unit\Services;

use Helpers\Unit;
use KikCMS\Services\CliService;
use Phalcon\Config\Config;
use Phalcon\Di\Di;

class CliServiceTest extends Unit
{
    public function testOutputLine()
    {
        $cliService = new CliService();
        $cliService->setDI(new Di);

        $cliService->config = new Config();
        $cliService->config->application = new Config();
        $cliService->config->application->showCliOutput = true;

        ob_start();
        $cliService->outputLine('test');
        $output = ob_get_clean();

        $this->assertStringContainsString('test', $output);

        $cliService->config->application->showCliOutput = false;

        ob_start();
        $cliService->outputLine('test');
        $output = ob_get_clean();

        $this->assertStringNotContainsString('test', $output);
    }
}
