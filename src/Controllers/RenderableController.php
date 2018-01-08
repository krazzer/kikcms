<?php

namespace KikCMS\Controllers;


use KikCMS\Classes\Exceptions\ObjectNotFoundException;
use KikCMS\Classes\Renderable\Renderable;

class RenderableController extends BaseController
{
    /**
     * @return Renderable
     */
    protected function getRenderable(): Renderable
    {
        $instance = $this->request->getPost(Renderable::FILTER_INSTANCE);
        $class    = $this->getClass();

        /** @var Renderable $renderable */
        $renderable = new $class();
        $renderable->setInstance($instance);

        $filters = $renderable->getEmptyFilters();
        $filters->setByArray($this->request->getPost());

        $renderable->setFilters($filters);

        return $renderable;
    }

    /**
     * @return string
     * @throws ObjectNotFoundException
     */
    protected function getClass(): string
    {
        $class = $this->request->getPost(Renderable::FILTER_CLASS);

        if( ! $class){
            throw new ObjectNotFoundException('RenderableClass');
        }

        return $class;
    }
}