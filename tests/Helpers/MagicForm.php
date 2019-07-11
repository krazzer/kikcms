<?php


namespace Helpers;


class MagicForm
{
    public function __set($x, $y)
    {
    }

    public function __get($x)
    {
    }

    public function __call($name, $arguments)
    {
        return $this;
    }
}
