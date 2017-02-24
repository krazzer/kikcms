<?php

namespace KikCMS\Services\Model;

use KikCMS\Classes\DbService;
use KikCMS\Models\Field;
use KikCMS\Models\Page;
use KikCMS\Models\TemplateField;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Resultset;

/**
 * Service for handling the Field Model objects
 *
 * @property DbService dbService
 */
class FieldService extends Injectable
{
    /**
     * @param Page $page
     * @return Resultset
     */
    public function getByPage(Page $page): Resultset
    {
        $query = new Builder();
        $query->from(['f' => Field::class]);
        $query->join(TemplateField::class, 'tf.field_id = f.id', 'tf');
        $query->where('tf.template_id = :templateId:', ['templateId' => $page->template_id]);

        return $query->getQuery()->execute();
    }
}