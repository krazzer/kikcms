<?php

namespace KikCMS\DataTables;


class SubProducts extends Products
{
    /** @inheritdoc */
    protected $parentRelationKey = 'parent_id';

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        parent::initialize();

        $this->setLimit(10);

        $this->setFieldFormatting('title', function ($value) {
            $value = html_entity_decode(strip_tags($value));

            if (mb_strlen($value) > 25) {
                return mb_substr($value, 0, 25) . '...';
            }

            return $value;
        });

        $this->setFieldFormatting('description', function ($value) {
            $value = html_entity_decode(strip_tags($value));

            if (mb_strlen($value) > 25) {
                return mb_substr($value, 0, 25) . '...';
            }

            return $value;
        });
    }
}