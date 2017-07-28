<?php

namespace KikCMS\Services\Pages;

use KikCMS\Classes\DbService;
use KikCMS\Models\Field;
use KikCMS\Models\Page;
use KikCMS\Models\Template;
use KikCMS\Models\TemplateField;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Resultset;

/**
 * Service for handling the Field Model objects
 *
 * @property DbService dbService
 */
class TemplateService extends Injectable
{
    /**
     * @param int $templateId
     * @return Resultset
     */
    public function getFieldsByTemplateId(int $templateId): Resultset
    {
        $query = new Builder();
        $query->from(['f' => Field::class]);
        $query->join(TemplateField::class, 'tf.field_id = f.id', 'tf');
        $query->where('tf.template_id = :templateId:', ['templateId' => $templateId]);
        $query->orderBy('tf.display_order ASC');

        return $query->getQuery()->execute();
    }

    /**
     * @return null|Template
     */
    public function getDefaultTemplate(): ?Template
    {
        /** @var Template $firstTemplate */
        $firstTemplate = Template::findFirst(['order' => 'display_order ASC']);

        return $firstTemplate;
    }

    /**
     * @param int $editId
     * @return null|Template
     */
    public function getTemplateByPageId(int $editId): ?Template
    {
        $query = new Builder();
        $query->from(['t' => Template::class]);
        $query->join(Page::class, 'p.template_id = t.id AND p.id = ' . $editId, 'p');

        return $query->getQuery()->execute()->getFirst();
    }
}