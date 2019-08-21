<?php declare(strict_types=1);

namespace KikCMS\Classes\Frontend;


use Phalcon\Di\Injectable;

/**
 * Represents a Class that can be extended by the Website
 */
class WebsiteExtendable extends Injectable
{
    /**
     * @param string $methodName
     * @throws \Exception
     */
    protected function throwMethodDoesNotExistException(string $methodName)
    {
        throw new \Exception('Method ' . get_class($this) . '::' . $methodName . ' not found');
    }
}