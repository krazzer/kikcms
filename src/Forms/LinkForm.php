<?php

namespace KikCMS\Forms;


use KikCMS\Classes\Permission;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\StringLength;

class LinkForm extends PageForm
{
    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addTextField(PageLanguage::FIELD_NAME, $this->translator->tl('fields.name'), [new PresenceOf()])
            ->table(PageLanguage::class, PageLanguage::FIELD_PAGE_ID, true);

        $this->addTextField(Page::FIELD_LINK, $this->translator->tl('dataTables.pages.linkToDesc'));
        $this->addHiddenField(Page::FIELD_TYPE, Page::TYPE_LINK);

        $urlPatternValidation = new Regex([
            'pattern'    => '/^$|^([0-9a-z\-]+)$/',
            'message'    => $this->translator->tl('webform.messages.slug'),
            'allowEmpty' => true,
        ]);

        $urlValidation = [$urlPatternValidation, new StringLength(["max" => 255])];

        $this->addTextField(PageLanguage::FIELD_URL, $this->translator->tl('fields.url'), $urlValidation)
            ->table(PageLanguage::class, PageLanguage::FIELD_PAGE_ID, true)
            ->setHelpText($this->translator->tl('dataTables.pages.urlLinkHelpText'));

        if ($this->acl->allowed(Permission::PAGE_KEY, Permission::ACCESS_EDIT)) {
            $this->addTextField(Page::FIELD_KEY, $this->translator->tl('fields.key'), [
                $urlPatternValidation,
                new StringLength(["max" => 32])
            ]);
        }
    }
}