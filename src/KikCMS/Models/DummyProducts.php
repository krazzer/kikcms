<?php

namespace KikCMS\Models;

use Phalcon\Mvc\Model;

class DummyProducts extends Model
{
    public function initialize()
    {
        $this->setSource('products_dummy');
    }
}