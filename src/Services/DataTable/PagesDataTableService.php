<?php declare(strict_types=1);


namespace KikCMS\Services\DataTable;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Page\Template;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Models\Page;
use KikCMS\Services\Cms\UserSettingsService;
use KikCMS\Services\Pages\PageService;
use KikCMS\Services\Pages\TemplateService;
use KikCMS\Classes\Phalcon\Injectable;

/**
 * @property PageService $pageService
 * @property TemplateService $templateService
 * @property UserSettingsService $userSettingsService
 */
class PagesDataTableService extends Injectable
{
    /**
     * @param $value
     * @param array $iconTitleMap
     * @param bool $isClosed
     * @return string
     */
    public function formatName($value, array $iconTitleMap, bool $isClosed): string
    {
        foreach ($iconTitleMap as $icon => $title){
            $value = '<span class="glyphicon glyphicon-' . $icon . '" title="' . $title . '"></span> ' . $value;
        }

        return '<span class="arrow' . ($isClosed ? ' closed' : '') . '"></span><span class="name">' . $value . '</span>';
    }

    /**
     * @param Renderable|DataTable|DataForm $renderable
     * @return Template|null
     */
    public function getTemplate(Renderable $renderable): ?Template
    {
        $templateKey = $this->request->getPost('template');

        if ($templateKey) {
            return $this->templateService->getByKey($templateKey);
        }

        $editId = $renderable->getFilters()->getEditId();

        if ($editId) {
            if ($template = $this->templateService->getTemplateByPageId($editId)) {
                return $template;
            }
        }

        if ($firstTemplate = $this->templateService->getDefaultTemplate()) {
            return $firstTemplate;
        }

        return null;
    }

    /**
     * @param $value
     * @param array $rowData
     * @return string
     */
    public function getValue($value, array $rowData): string
    {
        if ($rowData[Page::FIELD_TYPE] == Page::TYPE_MENU) {
            return (string) $rowData['default_language_name'];
        }

        if ( ! $value && $rowData['default_language_name']) {
            return '<span class="defaultLanguagePlaceHolder">' . $rowData['default_language_name'] . '</span>';
        }

        return (string) $value;
    }
}