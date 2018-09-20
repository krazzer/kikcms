<?php

namespace KikCMS\Forms;


use KikCMS\Classes\Permission;
use KikCMS\Models\Page;
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
        $this->addTextField('pageLanguage*:name', $this->translator->tl('fields.name'), [new PresenceOf()]);

        $this->addTextField(Page::FIELD_LINK, $this->translator->tl('dataTables.pages.linkToDesc'));
        $this->addHiddenField(Page::FIELD_TYPE, Page::TYPE_LINK);

        $urlPatternValidation = new Regex([
            'pattern'    => '/^$|^([0-9a-z\-]+)$/',
            'message'    => $this->translator->tl('webform.messages.slug'),
            'allowEmpty' => true,
        ]);

        $urlValidation = [$urlPatternValidation, new StringLength(["max" => 255])];

        $this->addTextField('pageLanguage*:url', $this->translator->tl('fields.url'), $urlValidation)
            ->setHelpText($this->translator->tl('dataTables.pages.urlLinkHelpText'));

        if ($this->acl->allowed(Permission::PAGE_KEY, Permission::ACCESS_EDIT)) {
            $this->addTextField(Page::FIELD_KEY, $this->translator->tl('fields.key'), [
                $urlPatternValidation,
                new StringLength(["max" => 32])
            ]);
        }
    }
}