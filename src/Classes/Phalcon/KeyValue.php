<?php declare(strict_types=1);


namespace KikCMS\Classes\Phalcon;


use Phalcon\Cache\BackendInterface;

/**
 * Extends BackendInterface to provide a logical name for using a KeyValue storage
 */
interface KeyValue extends BackendInterface
{

}