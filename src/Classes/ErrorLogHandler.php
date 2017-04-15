<?php

namespace KikCMS\Classes;

use Monolog\Handler\AbstractProcessingHandler;

/**
 * Error handler that just calls error_log, without all the mumbo jumbo
 */
class ErrorLogHandler extends AbstractProcessingHandler
{
    /**
     * @inheritdoc
     */
    protected function write(array $record)
    {
        error_log($record['message'] . PHP_EOL);
    }
}