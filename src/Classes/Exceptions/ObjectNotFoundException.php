<?php

namespace KikCMS\Classes\Exceptions;


class ObjectNotFoundException extends NotFoundException
{
    /** @var string */
    private $object;

    /**
     * @param string $object
     */
    public function __construct(string $object)
    {
        $this->object = $object;

        parent::__construct();
    }

    /**
     * @return string
     */
    public function getObject(): string
    {
        return $this->object;
    }
}