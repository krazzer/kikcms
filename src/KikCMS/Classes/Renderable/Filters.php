<?php

namespace KikCMS\Classes\Renderable;

/**
 * Value object that contains data that will influence how a Renderable object will render
 */
class Filters
{
    /** @var array */
    const FILTER_TYPES = [];

    /**
     * @param array $filters
     */
    public function setByArray(array $filters)
    {
        foreach (static::FILTER_TYPES as $filterType) {
            if (array_key_exists($filterType, $filters)) {
                $setMethod = 'set' . $filterType;
                $this->$setMethod($filters[$filterType]);
            }
        }
    }
}