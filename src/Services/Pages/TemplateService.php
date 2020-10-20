<?php declare(strict_types=1);

namespace KikCMS\Services\Pages;

use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Classes\WebForm\Fields\FileField;
use KikCMS\Classes\WebForm\Fields\WysiwygField;
use KikCMS\Classes\WebForm\Tab;
use KikCmsCore\Services\DbService;
use KikCMS\Classes\Frontend\Extendables\TemplateFieldsBase;
use KikCMS\Classes\Page\Template;
use KikCMS\Classes\WebForm\Field;
use KikCMS\Models\Page;
use KikCMS\ObjectLists\FieldMap;

/**
 * Service for handling Templates & Fields defined in TemplateFields
 *
 * @property DbService $dbService
 * @property TemplateFieldsBase $templateFields
 */
class TemplateService extends Injectable
{
    /**
     * @param string $templateKey
     * @return Template|null
     */
    public function getByKey(string $templateKey): ?Template
    {
        $templates = $this->templateFields->getTemplates();

        foreach ($templates as $template) {
            if ($template->getKey() == $templateKey) {
                return $template;
            }
        }

        return null;
    }

    /**
     * @param Template $template
     * @return FieldMap
     */
    public function getFieldsByTemplate(Template $template): FieldMap
    {
        $fieldMap = new FieldMap();

        foreach ($template->getFields() as $fieldKey) {
            if ($field = $this->getFieldByKey($fieldKey)) {
                $fieldMap->add($field, $fieldKey);
            }
        }

        return $fieldMap;
    }

    /**
     * @return null|Template
     */
    public function getDefaultTemplate(): ?Template
    {
        $templates = $this->getAvailable();

        if ( ! isset($templates[0])) {
            return null;
        }

        return $templates[0];
    }

    /**
     * @param int $pageId
     * @return null|Template
     */
    public function getTemplateByPageId(int $pageId): ?Template
    {
        $page = Page::getById($pageId);

        if ( ! $page) {
            return null;
        }

        return $this->getByKey($page->getTemplate());
    }

    /**
     * @param string|null $currentTemplate
     * @return Template[]
     */
    public function getAvailable(string $currentTemplate = null): array
    {
        $allTemplates       = $this->templateFields->getTemplates();
        $pageKeyTemplateMap = $this->pageService->getKeyTemplateMap();

        $templates = [];

        foreach ($allTemplates as $template) {
            $templateForPageKey = $pageKeyTemplateMap[$template->getPageKey()] ?? null;

            // a page exists with this key, so remove as an option
            if ($templateForPageKey == $template->getKey() && $template->getKey() !== $currentTemplate) {
                continue;
            }

            $templates[] = $template;
        }

        return $templates;
    }

    /**
     * @param string|null $currentTemplate
     * @return array [string key => string name]
     */
    public function getAvailableNameMap(string $currentTemplate = null): array
    {
        $templates = $this->getAvailable($currentTemplate);
        $nameMap   = [];

        foreach ($templates as $template) {
            $nameMap[$template->getKey()] = $template->getName();
        }

        return $nameMap;
    }

    /**
     * @param string $fieldKey
     * @return Tab|Field|null
     */
    private function getFieldByKey(string $fieldKey)
    {
        $fields = $this->templateFields->getFields();

        if ( ! array_key_exists($fieldKey, $fields)) {
            return null;
        }

        return $fields[$fieldKey];
    }

    /**
     * @return array
     */
    public function getAllowedKeys(): array
    {
        $templates   = $this->templateFields->getTemplates();
        $allowedKeys = [];

        foreach ($templates as $template) {
            if ( ! $template->isHidden()) {
                $allowedKeys[] = $template->getKey();
            }
        }

        return $allowedKeys;
    }

    /**
     * Get an array of keys for all existing file fields
     *
     * @return array
     */
    public function getFileFieldKeys(): array
    {
        $fields = $this->templateFields->getFields();

        $fieldKeys = [];

        foreach ($fields as $key => $field) {
            if ($field instanceof FileField) {
                $fieldKeys[] = $key;
            }
        }

        return $fieldKeys;
    }

    /**
     * Get an array of keys for all existing wysiwyg fields
     *
     * @return array
     */
    public function getWysiwygFieldKeys(): array
    {
        $fields = $this->templateFields->getFields();

        $fieldKeys = [];

        foreach ($fields as $key => $field) {
            if ($field instanceof WysiwygField) {
                $fieldKeys[] = $key;
            }
        }

        return $fieldKeys;
    }
}