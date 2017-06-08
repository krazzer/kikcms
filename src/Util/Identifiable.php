<?php

namespace KikCMS\Util;

/**
 * An Identifiable is an Object that is identified by an Id
 */
class Identifiable
{
    private $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Identifiable
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}