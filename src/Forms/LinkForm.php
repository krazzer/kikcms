<?php declare(strict_types=1);

namespace KikCMS\Forms;


use KikCMS\Classes\Permission;
use KikCMS\Models\Page;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\Regex;
use Phalcon\Filter\Validation\Validator\StringLength;

class LinkForm extends PageForm
{
    /**
     * @inheritdoc
     */
    protected function initialize(): void
    {
        $langCode  = $this->getFilters()->getLanguageCode();
        $urlsRoute = $this->url->get('getUrls', $langCode);

        $urlPatternValidation = new Regex([
            'pattern'    => '/^$|^([0-9a-z\-]+)$/',
            'message'    => $this->translator->tl('webform.messages.slug'),
            'allowEmpty' => true,
        ]);

        $keyValidation = [$urlPatternValidation, new StringLength(["max" => 32])];
        $urlValidation = [$urlPatternValidation, new StringLength(["max" => 255])];

        $urlLabel    = $this->translator->tl('fields.slug');
        $nameLabel   = $this->translator->tl('fields.name');
        $urlHelpText = $this->translator->tl('dataTables.pages.urlLinkHelpText');
        $linkLabel   = $this->translator->tl('dataTables.pages.linkToDesc');

        $this->addTextField('pageLanguage*:name', $nameLabel, [new PresenceOf()]);
        $linkField = $this->addAutoCompleteField(Page::FIELD_LINK, $linkLabel, $urlsRoute);
        $this->addHiddenField(Page::FIELD_TYPE, Page::TYPE_LINK);
        $this->addTextField('pageLanguage*:slug', $urlLabel, $urlValidation)->setHelpText($urlHelpText);

        $this->addFieldTransformer(new UrlToId($linkField, $langCode));

        if ($this->acl->allowed(Permission::PAGE_KEY, Permission::ACCESS_EDIT)) {
            $this->addTextField(Page::FIELD_KEY, $this->translator->tl('fields.key'), $keyValidation);
        }
    }
}