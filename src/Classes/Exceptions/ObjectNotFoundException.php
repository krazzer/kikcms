<?php declare(strict_types=1);

namespace KikCMS\Classes\Exceptions;


class ObjectNotFoundException extends NotFoundException
{
    /** @var string|null */
    private $object;

    /**
     * @param string|null $object
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