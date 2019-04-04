<?php

namespace KikCMS\Classes\Exceptions;


class ObjectNotFoundException extends NotFoundException
{
    /** @var string|null */
    private $object;

    /**
     * @param string $object
     */
    public function __construct(string $object = null)
    {
        $this->object = $object;

        parent::__construct();
    }

    /**
     * @return string|null
     */
    public function getObject(): ?string
    {
        return $this->object;
    }
}