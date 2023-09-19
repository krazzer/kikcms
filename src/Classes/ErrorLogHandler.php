<?php declare(strict_types=1);

namespace KikCMS\Classes;

use Monolog\Handler\AbstractProcessingHandler;

/**
 * Error handler that just calls error_log, without all the mumbo jumbo
 */
class ErrorLogHandler extends AbstractProcessingHandler
{
    /** @var null|string */
    private $errorLogPath = null;

    /**
     * @inheritdoc
     */
    protected function write(array $record): void
    {
        if($errorLogPath = $this->getErrorLogPath()){
            if( ! is_dir(dirname($errorLogPath))){
                mkdir(dirname($errorLogPath));
            }

            error_log('[' . date('d.m.Y h:i:s') . '] ' . $record['message'] . PHP_EOL, 3, $errorLogPath);
        } else {
            error_log($record['message'] . PHP_EOL);
        }
    }

    /**
     * @return string|null
     */
    public function getErrorLogPath(): ?string
    {
        return $this->errorLogPath;
    }

    /**
     * @param string|null $errorLogPath
     */
    public function setErrorLogPath(?string $errorLogPath): void
    {
        $this->errorLogPath = $errorLogPath;
    }
}