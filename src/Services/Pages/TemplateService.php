<?php

namespace KikCMS\Services\Pages;

use KikCMS\Classes\DbService;
use KikCMS\Models\Field;
use KikCMS\Models\Page;
use KikCMS\Models\Template;
use KikCMS\Models\TemplateField;
use KikCMS\ObjectLists\FieldMap;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Service for handling the Field Model objects
 *
 * @property DbService dbService
 */
class TemplateService extends Injectable
{
    /**
     * @param int $templateId
     * @return FieldMap
     */
    public function getFieldsByTemplateId(int $templateId): FieldMap
    {
        $query = (new Builder)
            ->from(['f' => Field::class])
            ->join(TemplateField::class, 'tf.field_id = f.id', 'tf')
            ->where('tf.template_id = :templateId:', ['templateId' => $templateId])
            ->orderBy('tf.display_order ASC');

        return $this->dbService->getObjectMap($query, FieldMap::class);
    }

    /**
     * @return null|Template
     */
    public function getDefaultTemplate(): ?Template
    {
        if ( ! $firstTemplate = Template::findFirst(['order' => 'display_order ASC'])) {
            return null;
        }

        return $firstTemplate;
    }

    /**
     * @param int $editId
     * @return null|Template
     */
    public function getTemplateByPageId(int $editId): ?Template
    {
        $query = (new Builder)
            ->from(['t' => Template::class])
            ->join(Page::class, 'p.template_id = t.id AND p.id = ' . $editId, 'p');

        return $this->dbService->getObject($query);
    }
}