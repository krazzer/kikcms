<?php

namespace KikCMS\Classes\Phalcon\FormElements;

use Phalcon\Forms\Element;

class MultiCheck extends Element
{
    /** @var array */
    private $options;

    /**
     * @inheritdoc
     */
    public function render($attributes = null)
    {
        // is rendered in twig
    }

    /**
     * @param $key
     * @return bool
     */
    public function isset($key): bool
    {
        return in_array($key, (array) $this->getValue());
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}