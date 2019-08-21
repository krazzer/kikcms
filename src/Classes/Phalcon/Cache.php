<?php declare(strict_types=1);


namespace KikCMS\Classes\Phalcon;


use Phalcon\Cache\BackendInterface;

/**
 * Extends BackendInterface to provide a logical name for using cache storage
 */
interface Cache extends BackendInterface
{

}