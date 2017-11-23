<?php


namespace KikCMS\Services\DataTable;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Page\Template;
use KikCMS\Classes\Renderable\Renderable;
use KikCMS\Classes\WebForm\DataForm\DataForm;
use Phalcon\Di\Injectable;

class PagesDataTableService extends Injectable
{
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
}