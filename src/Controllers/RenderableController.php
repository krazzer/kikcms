<?php

namespace KikCMS\Controllers;


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
     */
    protected function getClass(): string
    {
        return $this->request->getPost(Renderable::FILTER_CLASS);
    }
}