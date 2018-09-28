<?php

namespace KikCMS\Classes\Renderable;

/**
 * Value object that contains data that will influence how a Renderable object will render
 */
class Filters
{
    /**
     * @param array $filters
     */
    public function setByArray(array $filters)
    {
        foreach ($filters as $filterType => $value) {
            $setMethod = 'set' . $filterType;

            if($value === ''){
                $value = null;
            }

            if (method_exists($this, $setMethod)) {
                $this->$setMethod($value);
            }
        }
    }
}